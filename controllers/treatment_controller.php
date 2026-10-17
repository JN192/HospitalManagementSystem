<?php
require_once __DIR__ . '/../models/Treatment.php';
require_once __DIR__ . '/../models/Admission.php';
require_once __DIR__ . '/../models/Doctor.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $data = [
        'admission_id'            => $_POST['admission_id'] ?? '',
        'doctor_id'               => $_POST['doctor_id'] ?? '',
        'treatment_date'          => $_POST['treatment_date'] ?? '',
        'diagnosis'               => trim($_POST['diagnosis'] ?? ''),
        'symptoms'                => trim($_POST['symptoms'] ?? ''),
        'follow_up_instructions'  => trim($_POST['follow_up_instructions'] ?? ''),
        'treatment_description'   => trim($_POST['treatment_description'] ?? ''),
        'notes'                   => trim($_POST['notes'] ?? ''),
    ];

    if ($data['admission_id'] === '') {
        $errors[] = 'Treatment requires a valid active admission.';
    }
    if ($data['doctor_id'] === '') {
        $errors[] = 'Attending doctor is required.';
    }
    if ($data['treatment_date'] === '') {
        $errors[] = 'Treatment date is required.';
    }
    if ($data['diagnosis'] === '') {
        $errors[] = 'Diagnosis is required.';
    }
    if ($data['treatment_description'] === '') {
        $errors[] = 'Treatment description is required.';
    }

    if (empty($errors)) {
        Treatment::create($data);
        header('Location: index.php?page=treatment&success=1');
        exit;
    }
}

$admissions = Admission::getCurrentlyAdmitted();
$doctors = Doctor::getActiveDoctors();
$treatments = Treatment::getAll();

require_once __DIR__ . '/../views/treatment.php';