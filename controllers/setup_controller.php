<?php
require_once __DIR__ . '/../models/Auth.php';

if (Auth::hasUsers()) {
    header('Location: index.php?page=login');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedUsername = $_POST['username'] ?? '';
    $submittedPassword = $_POST['password'] ?? '';
    $submittedConfirmation = $_POST['confirm_password'] ?? '';
    $username = is_string($submittedUsername) ? trim($submittedUsername) : '';
    $password = is_string($submittedPassword) ? $submittedPassword : '';
    $confirmPassword = is_string($submittedConfirmation) ? $submittedConfirmation : '';

    if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Refresh the page and try again.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
        $error = 'Use 3–50 characters for the username: letters, numbers, dots, underscores, or hyphens.';
    } elseif (strlen($password) < 12 || strlen($password) > 72) {
        $error = 'Choose a password between 12 and 72 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } else {
        Auth::createFirstAdmin($username, $password);
        $user = Auth::authenticate($username, $password);
        if ($user === null) {
            throw new RuntimeException('The initial administrator account could not be authenticated.');
        }
        Auth::establishSession($user);
        header('Location: index.php?page=dashboard');
        exit;
    }
}

require __DIR__ . '/../views/setup.php';
