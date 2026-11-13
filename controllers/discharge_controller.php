<?php
require_once __DIR__ . '/../models/Discharge.php';

$errors = [];
$success = '';
$today = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'discharge') {
    $admissionId = filter_var($_POST['admission_id'] ?? null, FILTER_VALIDATE_INT);
    $submittedDate = $_POST['discharge_date'] ?? '';
    $submittedDiagnosis = $_POST['discharge_diagnosis'] ?? '';
    $submittedInstructions = $_POST['discharge_instructions'] ?? '';
    $submittedFollowUp = $_POST['follow_up_date'] ?? '';
    $data = [
        'discharge_date' => is_string($submittedDate) ? $submittedDate : '',
        'discharge_diagnosis' => is_string($submittedDiagnosis) ? trim($submittedDiagnosis) : '',
        'discharge_instructions' => is_string($submittedInstructions) ? trim($submittedInstructions) : '',
        'follow_up_date' => is_string($submittedFollowUp) ? $submittedFollowUp : '',
    ];

    $token = $_POST['csrf_token'] ?? null;
    if (!Auth::verifyCsrfToken(is_string($token) ? $token : null)) {
        $errors[] = 'Your form session expired. Refresh the page and try again.';
    } elseif ($admissionId === false || $admissionId === null || $admissionId <= 0) {
        $errors[] = 'Choose a valid active admission.';
    } else {
        $date = DateTime::createFromFormat('!Y-m-d', $data['discharge_date']);
        if (!$date || $date->format('Y-m-d') !== $data['discharge_date']) {
            $errors[] = 'Enter a valid discharge date.';
        }
        if ($data['discharge_diagnosis'] === '') {
            $errors[] = 'Discharge diagnosis is required.';
        }
        if ($data['discharge_instructions'] === '') {
            $errors[] = 'Discharge instructions are required.';
        }
        if ($data['follow_up_date'] !== '') {
            $followUp = DateTime::createFromFormat('!Y-m-d', $data['follow_up_date']);
            if (!$followUp || $followUp->format('Y-m-d') !== $data['follow_up_date']) {
                $errors[] = 'Enter a valid follow-up date.';
            } elseif ($date && $followUp < $date) {
                $errors[] = 'Follow-up date cannot be before discharge.';
            }
        }

        if ($errors === []) {
            try {
                Discharge::create((int)$admissionId, $data);
                $success = 'Patient discharged and room released successfully.';
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
}

$activeAdmissions = Discharge::getActiveAdmissions();
require __DIR__ . '/../views/discharge.php';
