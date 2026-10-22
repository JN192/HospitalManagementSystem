<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hospital Management System</title>
    <link rel="stylesheet" href="/HospitalManagementSystem/css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">HMS</div>
        <nav>
            <ul class="nav-menu">
                <li><a href="index.php?page=dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="index.php?page=patients" class="<?= $currentPage === 'patients' ? 'active' : '' ?>">Patients</a></li>
                <li><a href="index.php?page=courses" class="<?= $currentPage === 'courses' ? 'active' : '' ?>">Courses</a></li>
                <li><a href="index.php?page=doctors" class="<?= $currentPage === 'doctors' ? 'active' : '' ?>">Doctors</a></li>
                <li><a href="index.php?page=rooms" class="<?= $currentPage === 'rooms' ? 'active' : '' ?>">Rooms</a></li>
                <li><a href="index.php?page=admissions" class="<?= $currentPage === 'admissions' ? 'active' : '' ?>">Admissions</a></li>
                <li><a href="index.php?page=admissions_monitor" class="<?= $currentPage === 'admissions_monitor' ? 'active' : '' ?>">Admitted Patients</a></li>
                <li><a href="index.php?page=treatment" class="<?= $currentPage === 'treatment' ? 'active' : '' ?>">Treatment</a></li>
                <li><a href="index.php?page=medicines" class="<?= $currentPage === 'medicines' ? 'active' : '' ?>">Medicines</a></li>
                <li><a href="index.php?page=medication_orders" class="<?= $currentPage === 'medication_orders' ? 'active' : '' ?>">Medication Orders</a></li>
                <li><a href="index.php?page=services" class="<?= $currentPage === 'services' ? 'active' : '' ?>">Services &amp; Rates</a></li>
                <li><a href="index.php?page=billing" class="<?= $currentPage === 'billing' ? 'active' : '' ?>">Billing</a></li>
                <li><a href="index.php?page=payments" class="<?= $currentPage === 'payments' ? 'active' : '' ?>">Payments</a></li>
                <li><a href="index.php?page=discharge" class="<?= $currentPage === 'discharge' ? 'active' : '' ?>">Discharge</a></li>
                <li><a href="#">Reports</a></li>
                <li><a href="#">Users</a></li>
                <li><a href="#">Logout</a></li>
            </ul>
        </nav>
    </aside>
    <main class="content">
        