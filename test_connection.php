<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    $status = 'success';
    $message = 'Connected to the "' . DB_NAME . '" database successfully.';
} catch (PDOException $e) {
    $status = 'error';
    $message = 'Connection failed: ' . $e->getMessage();
}

require_once __DIR__ . '/views/header.php';
?>

    <h1>Database Connection Test</h1>
    <div class="card <?= $status === 'success' ? 'card-success' : 'card-error' ?>">
        <p><?= htmlspecialchars($message) ?></p>
    </div>
    <a class="btn" href="index.php">Back to Dashboard</a>

<?php require_once __DIR__ . '/views/footer.php'; ?>
