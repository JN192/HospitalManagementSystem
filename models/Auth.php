<?php
require_once __DIR__ . '/../config/database.php';

class Auth
{
    private const PAGE_ACCESS = [
        'Administrator' => ['*'],
        'Doctor' => ['patients', 'patient_profile', 'admissions', 'admissions_monitor', 'treatment'],
        'Nurse' => ['patients', 'patient_profile', 'admissions_monitor', 'treatment'],
        'Receptionist' => ['patients', 'patient_profile', 'admissions', 'admissions_monitor'],
        'Cashier' => ['billing', 'payments', 'receipt', 'statement'],
        'Manager' => ['dashboard', 'reports'],
    ];

    private const ROLES = ['Administrator', 'Doctor', 'Nurse', 'Receptionist', 'Cashier', 'Manager'];
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

    public static function verifyCsrfToken(mixed $token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function hasUsers(): bool
    {
        return (int)getDBConnection()->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
    }

    public static function roles(): array
    {
        return self::ROLES;
    }

    public static function canAccessPage(string $role, string $page): bool
    {
        if ($page === 'change_password') {
            return true;
        }

        $normalizedRole = strtolower(trim($role));
        if (in_array($normalizedRole, ['admin', 'administrator'], true)) {
            $role = 'Administrator';
        } else {
            foreach (self::ROLES as $knownRole) {
                if (strtolower($knownRole) === $normalizedRole) {
                    $role = $knownRole;
                    break;
                }
            }
        }

        $allowedPages = self::PAGE_ACCESS[$role] ?? [];
        return in_array('*', $allowedPages, true) || in_array($page, $allowedPages, true);
    }

    public static function homePage(string $role): string
    {
        foreach (['dashboard', 'patients', 'billing', 'reports'] as $page) {
            if (self::canAccessPage($role, $page)) {
                return $page;
            }
        }

        return 'dashboard';
    }

    public static function getUsers(): array
    {
        return getDBConnection()->query(
            'SELECT user_id, username, role, status FROM users ORDER BY user_id'
        )->fetchAll();
    }

    public static function createUser(string $username, string $password, string $role): void
    {
        if (!in_array($role, self::ROLES, true)) {
            throw new InvalidArgumentException('Choose a valid user role.');
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            $nextId = (int)$pdo->query('SELECT COALESCE(MAX(user_id), 0) + 1 FROM users')->fetchColumn();
            $stmt = $pdo->prepare(
                "INSERT INTO users (user_id, username, password, role, status)
                 VALUES (:user_id, :username, :password, :role, 'ACTIVE')"
            );
            $stmt->execute([
                'user_id' => $nextId,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
            ]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function setUserStatus(int $userId, string $status, int $currentUserId): void
    {
        if (!in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            throw new InvalidArgumentException('Choose a valid account status.');
        }
        if ($userId === $currentUserId && $status !== 'ACTIVE') {
            throw new RuntimeException('You cannot deactivate your own account.');
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT role, status FROM users WHERE user_id = :user_id FOR UPDATE');
            $stmt->execute(['user_id' => $userId]);
            $target = $stmt->fetch();
            if (!$target) {
                throw new RuntimeException('User account not found.');
            }

            if ($status === 'INACTIVE'
                && $target['status'] === 'ACTIVE'
                && in_array(strtolower($target['role']), ['admin', 'administrator'], true)) {
                $adminCount = (int)$pdo->query(
                    "SELECT COUNT(*) FROM users
                     WHERE status = 'ACTIVE' AND LOWER(role) IN ('admin', 'administrator')"
                )->fetchColumn();
                if ($adminCount <= 1) {
                    throw new RuntimeException('The last active administrator cannot be deactivated.');
                }
            }

            $update = $pdo->prepare('UPDATE users SET status = :status WHERE user_id = :user_id');
            $update->execute(['status' => $status, 'user_id' => $userId]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function setUserRole(int $userId, string $role, int $currentUserId): void
    {
        if (!in_array($role, self::ROLES, true)) {
            throw new InvalidArgumentException('Choose a valid user role.');
        }
        if ($userId === $currentUserId) {
            throw new RuntimeException('You cannot change your own role.');
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT role, status FROM users WHERE user_id = :user_id FOR UPDATE');
            $stmt->execute(['user_id' => $userId]);
            $target = $stmt->fetch();
            if (!$target) {
                throw new RuntimeException('User account not found.');
            }

            $wasAdmin = in_array(strtolower($target['role']), ['admin', 'administrator'], true);
            if ($target['status'] === 'ACTIVE' && $wasAdmin && $role !== 'Administrator') {
                $adminCount = (int)$pdo->query(
                    "SELECT COUNT(*) FROM users
                     WHERE status = 'ACTIVE' AND LOWER(role) IN ('admin', 'administrator')"
                )->fetchColumn();
                if ($adminCount <= 1) {
                    throw new RuntimeException('The last active administrator cannot be assigned another role.');
                }
            }

            $update = $pdo->prepare('UPDATE users SET role = :role WHERE user_id = :user_id');
            $update->execute(['role' => $role, 'user_id' => $userId]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function resetUserPassword(int $userId, string $password): void
    {
        $stmt = getDBConnection()->prepare('UPDATE users SET password = :password WHERE user_id = :user_id');
        $stmt->execute([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_id' => $userId,
        ]);
        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('User account not found or password was not changed.');
        }
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
