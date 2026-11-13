<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied | Hospital Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <main class="auth-card">
        <h1>Access Denied</h1>
        <p>Your <?= htmlspecialchars($_SESSION['auth_user']['role'], ENT_QUOTES, 'UTF-8') ?> role does not have permission to access this page.</p>
        <a class="btn" href="index.php?page=<?= htmlspecialchars(Auth::homePage($_SESSION['auth_user']['role']), ENT_QUOTES, 'UTF-8') ?>">Return to your home page</a>
    </main>
</body>
</html>
