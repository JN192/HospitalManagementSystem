<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hospital Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">HMS</div>
        <nav>
            <ul class="nav-menu">
                <?php
                $navigationItems = [
                    'dashboard' => 'Dashboard',
                    'patients' => 'Patients',
                    'courses' => 'Courses',
                    'doctors' => 'Doctors',
                    'rooms' => 'Rooms',
                    'admissions' => 'Admissions',
                    'admissions_monitor' => 'Admitted Patients',
                    'treatment' => 'Treatment',
                    'medicines' => 'Medicines',
                    'medication_orders' => 'Medication Orders',
                    'services' => 'Services &amp; Rates',
                    'billing' => 'Billing',
                    'payments' => 'Payments',
                    'discharge' => 'Discharge',
                    'reports' => 'Reports',
                    'users' => 'User Management',
                    'change_password' => 'Change Password',
                ];
                foreach ($navigationItems as $navPage => $label):
                    if (Auth::canAccessPage($_SESSION['auth_user']['role'], $navPage)):
                ?>
                    <li><a href="index.php?page=<?= htmlspecialchars($navPage, ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentPage === $navPage ? 'active' : '' ?>"><?= $label ?></a></li>
                <?php
                    endif;
                endforeach;
                ?>
                <li>
                    <form method="post" action="index.php?page=logout">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                        <button class="nav-logout" type="submit">Logout (<?= htmlspecialchars($_SESSION['auth_user']['username'], ENT_QUOTES, 'UTF-8') ?>)</button>
                    </form>
                </li>
            </ul>
        </nav>
    </aside>
    <main class="content">