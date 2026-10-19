<?php
require_once __DIR__ . '/../models/Patient.php';

$editPatient = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $data = [
        'patient_code'      => trim($_POST['patient_code'] ?? ''),
        'first_name'        => trim($_POST['first_name'] ?? ''),
        'last_name'         => trim($_POST['last_name'] ?? ''),
        'birth_date'        => trim($_POST['birth_date'] ?? ''),
        'sex'               => trim($_POST['sex'] ?? ''),
        'contact_number'    => trim($_POST['contact_number'] ?? ''),
        'address'           => trim($_POST['address'] ?? ''),
        'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
    ];

    foreach (['patient_code', 'first_name', 'last_name', 'birth_date', 'sex'] as $required) {
        if ($data[$required] === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $required)) . ' is required.';
        }
    }

    if (empty($errors)) {
        $patientId = $_POST['patient_id'] ?? '';
        if ($patientId !== '') {
            Patient::update((int)$patientId, $data);
        } else {
            Patient::create($data);
        }
        header('Location: index.php?page=patients');
        exit;
    }
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'deactivate') {
        Patient::deactivate($id);
<<<<<<< HEAD
        header('Location: index.php?page=patients'); exit;
    }
    if ($_GET['action'] === 'activate') {
        Patient::activate($id);
        header('Location: index.php?page=patients'); exit;
=======
        header('Location: index.php?page=patients');
        exit;
    }
    if ($_GET['action'] === 'activate') {
        Patient::activate($id);
        header('Location: index.php?page=patients');
        exit;
>>>>>>> d59fea6 (fix patient warning)
    }
    if ($_GET['action'] === 'edit') {
        $editPatient = Patient::getById($id);
    }
}

$search = trim($_GET['search'] ?? '');
$patients = Patient::getAll($search);

<<<<<<< HEAD
require_once __DIR__ . '/../views/patients.php';
=======
require_once __DIR__ . '/../views/patients.php';
>>>>>>> d59fea6 (fix patient warning)
