<?php
require_once __DIR__ . '/models/Auth.php';

Auth::startSession();
ob_start(); // lets controllers redirect with header() after the page header has printed
date_default_timezone_set('Asia/Manila'); // so "today" matches Philippine time on the Vercel server
error_reporting(E_ALL); ini_set('display_errors', 1);

$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'login', 'setup', 'logout', 'change_password', 'patients', 'patient_profile', 'courses', 'doctors', 'rooms', 'admissions', 'admissions_monitor', 'treatment', 'medicines', 'medication_orders', 'services', 'billing', 'payments', 'statement', 'receipt', 'discharge', 'reports'];
if (!in_array($page, $allowedPages, true)) { $page = 'dashboard'; }

if (isset($_SESSION['auth_user'])) {
   $now = time();
   if ($now - ($_SESSION['last_activity'] ?? 0) >= 1800) {
       Auth::destroySession();
       header('Location: index.php?page=login&timeout=1');
       exit;
   }

   $activeUser = Auth::getActiveUser((int)$_SESSION['auth_user']['user_id']);
   if ($activeUser === null) {
       Auth::destroySession();
       header('Location: index.php?page=login');
       exit;
   }
   $_SESSION['auth_user'] = $activeUser;
   $_SESSION['last_activity'] = $now;

   if ($now - ($_SESSION['last_regenerated'] ?? 0) >= 900) {
       session_regenerate_id(true);
       $_SESSION['last_regenerated'] = $now;
   }
}

if ($page === 'logout') {
   require __DIR__ . '/controllers/logout_controller.php';
}

if ($page === 'login') {
   if (isset($_SESSION['auth_user'])) {
       header('Location: index.php?page=dashboard');
       exit;
   }
   require __DIR__ . '/controllers/login_controller.php';
   exit;
}

if ($page === 'setup') {
   if (isset($_SESSION['auth_user'])) {
       header('Location: index.php?page=dashboard');
       exit;
   }
   require __DIR__ . '/controllers/setup_controller.php';
   exit;
}

if (!isset($_SESSION['auth_user'])) {
   header('Location: index.php?page=' . (Auth::hasUsers() ? 'login' : 'setup'));
   exit;
}

require_once __DIR__ . '/views/header.php';

if ($page === 'dashboard') {
   require_once __DIR__ . '/controllers/dashboard_controller.php';
} elseif ($page === 'change_password') {
   require_once __DIR__ . '/controllers/change_password_controller.php';
} elseif ($page === 'patients') {
   require_once __DIR__ . '/controllers/patients_controller.php';
} elseif ($page === 'patient_profile') {
   require_once __DIR__ . '/controllers/patient_profile_controller.php';
} elseif ($page === 'courses') {
   require_once __DIR__ . '/controllers/courses_controller.php';
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
} elseif ($page === 'discharge') {
   require_once __DIR__ . '/controllers/discharge_controller.php';
} elseif ($page === 'reports') {
   require_once __DIR__ . '/controllers/reports_controller.php';
} else {
   echo '<h1>Hospital Management System</h1>';
}

require_once __DIR__ . '/views/footer.php';