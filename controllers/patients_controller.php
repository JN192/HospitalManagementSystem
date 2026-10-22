<?php
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../models/Course.php';

$editPatient = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $data = [
        'patient_code'      => trim($_POST['patient_code'] ?? ''),
        'first_name'        => trim($_POST['first_name'] ?? ''),
        'last_name'         => trim($_POST['last_name'] ?? ''),
        'birth_date'        => $_POST['birth_date'] ?? '',
        'sex'               => $_POST['sex'] ?? '',
        'contact_number'    => trim($_POST['contact_number'] ?? ''),
        'address'           => trim($_POST['address'] ?? ''),
        'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
        'course_id'         => (int)($_POST['course_id'] ?? 0),
    ];
    $patientId = $_POST['patient_id'] ?? '';

    foreach (['patient_code', 'first_name', 'last_name', 'birth_date', 'sex'] as $required) {
        if ($data[$required] === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $required)) . ' is required.';
        }
    }

    // Course must be selected and must exist in the courses table
    if ($data['course_id'] <= 0) {
        $errors[] = 'Please select a course.';
    } elseif (!Course::getById($data['course_id'])) {
        $errors[] = 'The selected course does not exist.';
    }

    if (empty($errors)) {
        if ($patientId !== '') {
            Patient::update((int)$patientId, $data);
        } else {
            Patient::create($data);
        }
        header('Location: index.php?page=patients');
        exit;
    }

    // keep what the user typed when validation fails
    $editPatient = $data;
    $editPatient['patient_id'] = $patientId;
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'deactivate') {
        Patient::deactivate($id);
        header('Location: index.php?page=patients'); exit;
    }
    if ($_GET['action'] === 'activate') {
        Patient::activate($id);
        header('Location: index.php?page=patients'); exit;
    }
    if ($_GET['action'] === 'edit') {
        $editPatient = Patient::getById($id);
    }
}

$search = trim($_GET['search'] ?? '');
$patients = Patient::getAll($search);
$courses = Course::getAll();   // fills the Course dropdown from the database

require_once __DIR__ . '/../views/patients.php';