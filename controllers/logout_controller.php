<?php
require_once __DIR__ . '/../models/Auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Auth::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    echo 'Invalid logout request. Return to the application and try again.';
    exit;
}

Auth::destroySession();
header('Location: index.php?page=login');
exit;
