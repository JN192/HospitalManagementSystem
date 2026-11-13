<?php
require_once __DIR__ . '/../models/Auth.php';

$user = $_SESSION['auth_user'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedCurrentPassword = $_POST['current_password'] ?? '';
    $submittedNewPassword = $_POST['new_password'] ?? '';
    $submittedConfirmation = $_POST['confirm_password'] ?? '';
    $currentPassword = is_string($submittedCurrentPassword) ? $submittedCurrentPassword : '';
    $newPassword = is_string($submittedNewPassword) ? $submittedNewPassword : '';
    $confirmPassword = is_string($submittedConfirmation) ? $submittedConfirmation : '';
    if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Refresh the page and try again.';
    } elseif (Auth::authenticate($user['username'], $currentPassword) === null) {
        $error = 'The current password is incorrect.';
    } elseif (strlen($newPassword) < 12 || strlen($newPassword) > 72) {
        $error = 'Choose a password between 12 and 72 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'The new passwords do not match.';
    } elseif ($newPassword === $currentPassword) {
        $error = 'The new password must be different from the current password.';
    } else {
        Auth::changePassword((int)$user['user_id'], $newPassword);
        session_regenerate_id(true);
        $_SESSION['last_regenerated'] = time();
        $success = 'Password changed successfully.';
    }
}

require __DIR__ . '/../views/change_password.php';
