<?php
require_once __DIR__ . '/../models/Treatment.php';
require_once __DIR__ . '/../models/Admission.php';
require_once __DIR__ . '/../models/Doctor.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $readString = static function (string $key): string {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    };
    $data = [
        'admission_id'            => filter_var($_POST['admission_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
        'doctor_id'               => filter_var($_POST['doctor_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
        'treatment_date'          => $readString('treatment_date'),
        'diagnosis'               => $readString('diagnosis'),
        'symptoms'                => $readString('symptoms'),
        'follow_up_instructions'  => $readString('follow_up_instructions'),
        'treatment_description'   => $readString('treatment_description'),
        'notes'                   => $readString('notes'),
    ];

    if ($data['admission_id'] <= 0) {
        $errors[] = 'Treatment requires a valid active admission.';
    }
    if ($data['doctor_id'] <= 0) {
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
        try {
            Treatment::create($data);
            header('Location: index.php?page=treatment&success=1');
            exit;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$admissions = Admission::getCurrentlyAdmitted();
$doctors = Doctor::getActiveDoctors();
$treatments = Treatment::getAll();

require_once __DIR__ . '/../views/treatment.php';