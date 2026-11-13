<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Hospital Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <main class="auth-card">
        <h1>Hospital Management</h1>
        <p>Sign in to continue.</p>
        <?php if (isset($_GET['timeout'])): ?>
            <p class="auth-message">Your session expired. Please sign in again.</p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p class="auth-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="index.php?page=login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" maxlength="50" autocomplete="username" required autofocus>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button type="submit" <?= Auth::loginIsLocked() ? 'disabled' : '' ?>>Log In</button>
        </form>
    </main>
</body>
</html>
