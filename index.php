<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'patients', 'patient_profile', 'doctors', 'rooms', 'admissions', 'admissions_monitor', 'treatment', 'medicines', 'medication_orders', 'services', 'billing', 'payments', 'statement', 'receipt'];
if (!in_array($page, $allowedPages)) { $page = 'dashboard'; }

require_once __DIR__ . '/views/header.php';

if ($page === 'patients') {
    require_once __DIR__ . '/controllers/patients_controller.php';
} elseif ($page === 'patient_profile') {
    require_once __DIR__ . '/controllers/patient_profile_controller.php';
} elseif ($page === 'doctors') {
    require_once __DIR__ . '/controllers/doctors_controller.php';
} elseif ($page === 'rooms') {
    require_once __DIR__ . '/controllers/rooms_controller.php';
} elseif ($page === 'admissions') {
    require_once __DIR__ . '/controllers/admissions_controller.php';
} elseif ($page === 'admissions_monitor') {
    require_once __DIR__ . '/controllers/admission_monitoring_controller.php';
} elseif ($page === 'treatment') {
    require_once __DIR__ . '/controllers/treatment_controller.php';
} elseif ($page === 'medicines') {
    require_once __DIR__ . '/controllers/medicines_controller.php';
} elseif ($page === 'medication_orders') {
    require_once __DIR__ . '/controllers/medication_orders_controller.php';
} elseif ($page === 'services') {
    require_once __DIR__ . '/controllers/services_controller.php';
} elseif ($page === 'billing') {
    require_once __DIR__ . '/controllers/billing_controller.php';
} elseif ($page === 'payments') {
    require_once __DIR__ . '/controllers/payments_controller.php';
} elseif ($page === 'statement') {
    require_once __DIR__ . '/controllers/statement_controller.php';
} elseif ($page === 'receipt') {
    require_once __DIR__ . '/controllers/receipt_controller.php';
} else {
    echo '<h1>Hospital Management System</h1>';
}

require_once __DIR__ . '/views/footer.php';