<?php
require_once __DIR__ . '/../config/database.php';

class Auth
{
    private const FAILED_LOGIN_LIMIT = 5;
    private const FAILED_LOGIN_WINDOW = 900;
    private const LOCKOUT_SECONDS = 900;
    private const DUMMY_PASSWORD_HASH = '$2y$10$DBJ49r3Q2oN0uX8PcelWeO8VGzO3A620y4jEKEFal8HuKV1uDNH5.';

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $cookiePath = strpos($requestPath, '/HospitalManagementSystem/') === 0
            ? '/HospitalManagementSystem'
            : '/';
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $isHttps = $isHttps || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $cookiePath,
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function hasUsers(): bool
    {
        return (int)getDBConnection()->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
    }

    public static function authenticate(string $username, string $password): ?array
    {
        $stmt = getDBConnection()->prepare(
            'SELECT user_id, username, password, role, status FROM users WHERE username = :username LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        $hash = $user ? (string)$user['password'] : self::DUMMY_PASSWORD_HASH;
        $passwordMatches = password_verify($password, $hash);

        if (!$user || $user['status'] !== 'ACTIVE' || !$passwordMatches) {
            return null;
        }

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = getDBConnection()->prepare(
                'UPDATE users SET password = :password WHERE user_id = :user_id'
            );
            $update->execute(['password' => $newHash, 'user_id' => $user['user_id']]);
        }

        return [
            'user_id' => (int)$user['user_id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
    }

    public static function createFirstAdmin(string $username, string $password): void
    {
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            if ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() !== 0) {
                throw new RuntimeException('Initial setup is already complete.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO users (user_id, username, password, role, status)
                 VALUES (1, :username, :password, 'Administrator', 'ACTIVE')"
            );
            $stmt->execute([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function getActiveUser(int $userId): ?array
    {
        $stmt = getDBConnection()->prepare(
            "SELECT user_id, username, role FROM users WHERE user_id = :user_id AND status = 'ACTIVE'"
        );
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        return [
            'user_id' => (int)$user['user_id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
    }

    public static function changePassword(int $userId, string $password): void
    {
        $stmt = getDBConnection()->prepare(
            "UPDATE users SET password = :password WHERE user_id = :user_id AND status = 'ACTIVE'"
        );
        $stmt->execute([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_id' => $userId,
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('The password could not be updated for this account.');
        }
    }

    public static function loginIsLocked(): bool
    {
        return isset($_SESSION['login_lockout_until'])
            && $_SESSION['login_lockout_until'] > time();
    }

    public static function recordFailedLogin(): void
    {
        $now = time();
        if (!isset($_SESSION['login_window_started'])
            || $now - $_SESSION['login_window_started'] >= self::FAILED_LOGIN_WINDOW) {
            $_SESSION['login_window_started'] = $now;
            $_SESSION['failed_login_count'] = 0;
        }

        $_SESSION['failed_login_count'] = ($_SESSION['failed_login_count'] ?? 0) + 1;
        if ($_SESSION['failed_login_count'] >= self::FAILED_LOGIN_LIMIT) {
            $_SESSION['login_lockout_until'] = $now + self::LOCKOUT_SECONDS;
        }
    }

    public static function clearFailedLogins(): void
    {
        unset(
            $_SESSION['login_window_started'],
            $_SESSION['failed_login_count'],
            $_SESSION['login_lockout_until']
        );
    }

    public static function establishSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['auth_user'] = $user;
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regenerated'] = time();
        self::clearFailedLogins();
        unset($_SESSION['csrf_token']);
    }

    public static function destroySession(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
}
