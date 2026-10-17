<?php
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../models/Doctor.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Admission.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'admit') {
    $data = [
        'patient_id'      => $_POST['patient_id'] ?? '',
        'doctor_id'       => $_POST['doctor_id'] ?? '',
        'room_id'         => $_POST['room_id'] ?? '',
        'admission_date'  => $_POST['admission_date'] ?? '',
        'admission_time'  => $_POST['admission_time'] ?? '',
        'admission_type'  => $_POST['admission_type'] ?? '',
        'reason'          => trim($_POST['reason'] ?? ''),
    ];

    // Validation: required fields
    if ($data['patient_id'] === '') {
        $errors[] = 'Admission requires a patient to be selected.';
    }
    if ($data['doctor_id'] === '') {
        $errors[] = 'Admission requires an attending doctor.';
    }
    if ($data['room_id'] === '') {
        $errors[] = 'Admission requires a room/bed to be selected.';
    }
    if ($data['admission_type'] === '') {
        $errors[] = 'Admission type is required.';
    }
    if ($data['reason'] === '') {
        $errors[] = 'Reason for admission is required.';
    }

    // Validation: valid date, not in the future, not too far in the past
    if ($data['admission_date'] === '') {
        $errors[] = 'Admission date is required.';
    } else {
        $d = DateTime::createFromFormat('!Y-m-d', $data['admission_date']);
        if (!$d || $d->format('Y-m-d') !== $data['admission_date']) {
            $errors[] = 'Invalid admission date.';
        } else {
            $today = new DateTime('today');
            $oldest = (clone $today)->modify('-30 days');
            if ($d > $today) {
                $errors[] = 'Admission date cannot be in the future.';
            } elseif ($d < $oldest) {
                $errors[] = 'Admission date cannot be more than 30 days in the past.';
            }
        }
    }

    // Validation: room must still be available
    if (empty($errors) && $data['room_id'] !== '') {
        $roomFound = false;
        foreach (Room::getAvailableRooms() as $r) {
            if ((int)$r['room_id'] === (int)$data['room_id']) { $roomFound = true; break; }
        }
        if (!$roomFound) {
            $errors[] = 'The selected room/bed is currently occupied. Please select another available room.';
        }
    }

    // Validation: no duplicate active admission
    if (empty($errors) && $data['patient_id'] !== '') {
        if (Admission::hasActiveAdmission((int)$data['patient_id'])) {
            $errors[] = 'This patient already has an active admission. Discharge the current admission before admitting again.';
        }
    }

    if (empty($errors)) {
        Admission::create($data);
        header('Location: index.php?page=admissions&success=1');
        exit;
    }
}

$patients = Patient::getActivePatients();
$doctors  = Doctor::getActiveDoctors();
$rooms    = Room::getAvailableRooms();
$admissions = Admission::getAll();

require_once __DIR__ . '/../views/admissions.php';