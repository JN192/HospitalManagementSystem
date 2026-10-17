<?php
require_once __DIR__ . '/../models/Doctor.php';

$editDoctor = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $data = [
        'doctor_code'     => trim($_POST['doctor_code'] ?? ''),
        'doctor_name'     => trim($_POST['doctor_name'] ?? ''),
        'specialization'  => trim($_POST['specialization'] ?? ''),
        'department_id'   => $_POST['department_id'] ?? '',
        'contact_number'  => trim($_POST['contact_number'] ?? ''),
    ];

    foreach (['doctor_code', 'doctor_name', 'specialization', 'department_id'] as $required) {
        if ($data[$required] === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $required)) . ' is required.';
        }
    }

    if (empty($errors)) {
        $doctorId = $_POST['doctor_id'] ?? '';
        if ($doctorId !== '') {
            Doctor::update((int)$doctorId, $data);
        } else {
            Doctor::create($data);
        }
        header('Location: index.php?page=doctors');
        exit;
    }
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'deactivate') {
        Doctor::deactivate($id);
        header('Location: index.php?page=doctors'); exit;
    }
    if ($_GET['action'] === 'activate') {
        Doctor::activate($id);
        header('Location: index.php?page=doctors'); exit;
    }
    if ($_GET['action'] === 'edit') {
        $editDoctor = Doctor::getById($id);
    }   
}

$search = trim($_GET['search'] ?? '');
$doctors = Doctor::getAll($search);
$departments = Doctor::getAllDepartments();

require_once __DIR__ . '/../views/doctors.php';