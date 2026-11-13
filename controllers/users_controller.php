<?php
require_once __DIR__ . '/../models/Auth.php';

$errors = [];
$success = '';
$currentUserId = (int)$_SESSION['auth_user']['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Auth::verifyCsrfToken(is_string($submittedToken) ? $submittedToken : null)) {
        $errors[] = 'Your form session expired. Refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'create') {
                $submittedUsername = $_POST['username'] ?? '';
                $submittedPassword = $_POST['password'] ?? '';
                $username = is_string($submittedUsername) ? trim($submittedUsername) : '';
                $password = is_string($submittedPassword) ? $submittedPassword : '';
                $role = $_POST['role'] ?? '';

                if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
                    $errors[] = 'Use 3–50 characters for the username: letters, numbers, dots, underscores, or hyphens.';
                } elseif (strlen($password) < 12 || strlen($password) > 72) {
                    $errors[] = 'Choose a password between 12 and 72 characters.';
                } elseif (!in_array($role, Auth::roles(), true)) {
                    $errors[] = 'Choose a valid role.';
                } else {
                    Auth::createUser($username, $password, $role);
                    $success = 'User account created.';
                }
            } elseif ($action === 'status') {
                $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
                $submittedStatus = $_POST['status'] ?? '';
                $status = is_string($submittedStatus) ? $submittedStatus : '';
                if ($userId === false || $userId === null || $userId <= 0) {
                    $errors[] = 'Choose a valid user account.';
                } else {
                    Auth::setUserStatus((int)$userId, $status, $currentUserId);
                    $success = $status === 'ACTIVE' ? 'User account activated.' : 'User account deactivated.';
                }
            } elseif ($action === 'role') {
                $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
                $submittedRole = $_POST['role'] ?? '';
                $role = is_string($submittedRole) ? $submittedRole : '';
                if ($userId === false || $userId === null || $userId <= 0) {
                    $errors[] = 'Choose a valid user account.';
                } else {
                    Auth::setUserRole((int)$userId, $role, $currentUserId);
                    $success = 'User role updated.';
                }
            } elseif ($action === 'reset_password') {
                $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
                $submittedPassword = $_POST['password'] ?? '';
                $password = is_string($submittedPassword) ? $submittedPassword : '';
                if ($userId === false || $userId === null || $userId <= 0) {
                    $errors[] = 'Choose a valid user account.';
                } elseif (strlen($password) < 12 || strlen($password) > 72) {
                    $errors[] = 'Choose a password between 12 and 72 characters.';
                } else {
                    Auth::resetUserPassword((int)$userId, $password);
                    $success = 'User password reset.';
                }
            } else {
                $errors[] = 'Unknown user-management action.';
            }
        } catch (PDOException $e) {
            if ((string)$e->getCode() === '23000') {
                $errors[] = 'That username is already in use.';
            } else {
                throw $e;
            }
        } catch (RuntimeException | InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$users = Auth::getUsers();
require __DIR__ . '/../views/users.php';
