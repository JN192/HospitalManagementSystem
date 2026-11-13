<?php
require_once __DIR__ . '/../models/Auth.php';

if (Auth::hasUsers() === false) {
    header('Location: index.php?page=setup');
    exit;
}

$error = '';
if (Auth::loginIsLocked()) {
    $error = 'Too many unsuccessful attempts. Please wait 15 minutes before trying again.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Refresh the page and try again.';
    } else {
        $submittedUsername = $_POST['username'] ?? '';
        $submittedPassword = $_POST['password'] ?? '';
        $username = is_string($submittedUsername) ? trim($submittedUsername) : '';
        $password = is_string($submittedPassword) ? $submittedPassword : '';
        $user = Auth::authenticate($username, $password);

        if ($user !== null) {
            Auth::establishSession($user);
            header('Location: index.php?page=dashboard');
            exit;
        }

        Auth::recordFailedLogin();
        $error = Auth::loginIsLocked()
            ? 'Too many unsuccessful attempts. Please wait 15 minutes before trying again.'
            : 'Invalid username or password.';
    }
}

require __DIR__ . '/../views/login.php';
