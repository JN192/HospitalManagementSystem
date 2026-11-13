<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Initial Setup | Hospital Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <main class="auth-card">
        <h1>Set Up Hospital Management</h1>
        <p>Create the first administrator account. This setup closes after the account is created.</p>
        <?php if ($error !== ''): ?>
            <p class="auth-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="index.php?page=setup">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="username">Administrator username</label>
            <input id="username" name="username" type="text" minlength="3" maxlength="50" autocomplete="username" required autofocus>
            <label for="password">Password (12–72 characters)</label>
            <input id="password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
            <label for="confirm-password">Confirm password</label>
            <input id="confirm-password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
            <button type="submit">Create Administrator</button>
        </form>
    </main>
</body>
</html>
