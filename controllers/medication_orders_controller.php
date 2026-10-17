<?php
require_once __DIR__ . '/../models/Treatment.php';
require_once __DIR__ . '/../models/Medicine.php';
require_once __DIR__ . '/../config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'order') {
    $data = [
        'treatment_id' => $_POST['treatment_id'] ?? '',
        'medicine_id'  => $_POST['medicine_id'] ?? '',
        'dosage'       => trim($_POST['dosage'] ?? ''),
        'frequency'    => trim($_POST['frequency'] ?? ''),
        'duration'     => trim($_POST['duration'] ?? ''),
        'instructions' => trim($_POST['instructions'] ?? ''),
    ];

    if ($data['treatment_id'] === '') { $errors[] = 'Treatment record is required.'; }
    if ($data['medicine_id'] === '') { $errors[] = 'Medicine is required.'; }
    if ($data['dosage'] === '') { $errors[] = 'Dosage is required.'; }
    if ($data['frequency'] === '') { $errors[] = 'Frequency is required.'; }
    if ($data['duration'] === '') { $errors[] = 'Duration is required.'; }

    if (empty($errors)) {
        Treatment::addMedicineOrder($data);
        header('Location: index.php?page=medication_orders&success=1');
        exit;
    }
}

$pdo = getDBConnection();
$treatments = $pdo->query(
    "SELECT t.treatment_id, t.treatment_date, p.first_name, p.last_name
     FROM treatments t
     JOIN admissions a ON t.admission_id = a.admission_id
     JOIN patients p ON a.patient_id = p.patient_id
     ORDER BY t.treatment_id DESC"
)->fetchAll();

$medicines = Medicine::getAll();

$viewTreatmentId = isset($_GET['view_treatment']) ? (int)$_GET['view_treatment'] : null;
$orders = $viewTreatmentId ? Treatment::getMedicineOrders($viewTreatmentId) : [];

require_once __DIR__ . '/../views/medication_orders.php';