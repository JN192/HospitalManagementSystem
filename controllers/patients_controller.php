<?php
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../models/Course.php';

$editPatient = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $readString = static function (string $key): string {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    };
    $data = [
        'patient_code'      => $readString('patient_code'),
        'first_name'        => $readString('first_name'),
        'last_name'         => $readString('last_name'),
        'birth_date'        => $readString('birth_date'),
        'sex'               => $readString('sex'),
        'contact_number'    => $readString('contact_number'),
        'address'           => $readString('address'),
        'emergency_contact' => $readString('emergency_contact'),
        'course_id'         => filter_var($_POST['course_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
    ];
    $submittedPatientId = $_POST['patient_id'] ?? '';
    $patientId = is_string($submittedPatientId) ? trim($submittedPatientId) : '';

    foreach (['patient_code', 'first_name', 'last_name', 'birth_date', 'sex'] as $required) {
        if ($data[$required] === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $required)) . ' is required.';
        }
    }

    if ($data['patient_code'] !== '' && !preg_match('/^[A-Za-z0-9._-]{1,20}$/', $data['patient_code'])) {
        $errors[] = 'Patient ID/code must be 1-20 characters using letters, numbers, dots, underscores, or hyphens.';
    }
    foreach (['first_name' => 50, 'last_name' => 50] as $field => $maxLength) {
        if ($data[$field] !== '' && (mb_strlen($data[$field]) > $maxLength || preg_match('/[\x00-\x1F\x7F]/', $data[$field]))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is invalid or too long.';
        }
    }
    $birthDate = DateTime::createFromFormat('!Y-m-d', $data['birth_date']);
    if ($data['birth_date'] !== '' && (!$birthDate || $birthDate->format('Y-m-d') !== $data['birth_date'] || $birthDate > new DateTime('today'))) {
        $errors[] = 'Enter a valid birth date that is not in the future.';
    }
    if ($data['sex'] !== '' && !in_array($data['sex'], ['Male', 'Female'], true)) {
        $errors[] = 'Select Male or Female for sex.';
    }
    if (strlen($data['contact_number']) > 20 || ($data['contact_number'] !== '' && !preg_match('/^[0-9+(). -]{7,20}$/', $data['contact_number']))) {
        $errors[] = 'Enter a valid contact number (7-20 characters).';
    }
    if (strlen($data['address']) > 255 || strlen($data['emergency_contact']) > 100) {
        $errors[] = 'Address or emergency contact exceeds the allowed length.';
    }
    $currentPatient = null;
    if ($patientId !== '') {
        if (!ctype_digit($patientId) || (int)$patientId <= 0 || !($currentPatient = Patient::getById((int)$patientId))) {
            $errors[] = 'Select a valid existing patient to update.';
        }
    }
    if ($data['patient_code'] !== '' && ($currentPatient === null || $currentPatient['patient_code'] !== $data['patient_code']) && Patient::codeExists($data['patient_code'], (int)$patientId)) {
        $errors[] = 'A patient with this ID/code already exists.';
    }

    if ($data['course_id'] <= 0) {
        $errors[] = 'Please select a course.';
    } elseif (!Course::getById($data['course_id'])) {
        $errors[] = 'The selected course does not exist.';
    }

    if (empty($errors)) {
        try {
            if ($patientId !== '') {
                Patient::update((int)$patientId, $data);
            } else {
                Patient::create($data);
            }
            header('Location: index.php?page=patients');
            exit;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    // keep what the user typed when validation fails
    $editPatient = $data;
    $editPatient['patient_id'] = $patientId;
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        $errors[] = 'Choose a valid patient.';
    } else {
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
}

$search = trim($_GET['search'] ?? '');
$patients = Patient::getAll($search);
$courses = Course::getAll();   // fills the Course dropdown from the database

require_once __DIR__ . '/../views/patients.php';