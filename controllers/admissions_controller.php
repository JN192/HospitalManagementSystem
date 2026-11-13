<?php
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../models/Doctor.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Admission.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'admit') {
    $readString = static function (string $key): string {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    };
    $data = [
        'patient_id'      => filter_var($_POST['patient_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
        'doctor_id'       => filter_var($_POST['doctor_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
        'room_id'         => filter_var($_POST['room_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
        'admission_date'  => $readString('admission_date'),
        'admission_time'  => $readString('admission_time'),
        'admission_type'  => $readString('admission_type'),
        'reason'          => $readString('reason'),
    ];

    // Validation: required fields
    if ($data['patient_id'] <= 0) {
        $errors[] = 'Admission requires a patient to be selected.';
    }
    if ($data['doctor_id'] <= 0) {
        $errors[] = 'Admission requires an attending doctor.';
    }
    if ($data['room_id'] <= 0) {
        $errors[] = 'Admission requires a room/bed to be selected.';
    }
    if (!in_array($data['admission_type'], ['Emergency', 'Regular'], true)) {
        $errors[] = 'Select a valid admission type.';
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
    if (empty($errors) && $data['room_id'] > 0) {
        $roomFound = false;
        foreach (Room::getAvailableRooms() as $r) {
            if ((int)$r['room_id'] === $data['room_id']) { $roomFound = true; break; }
        }
        if (!$roomFound) {
            $errors[] = 'The selected room/bed is currently occupied. Please select another available room.';
        }
    }

    // Validation: no duplicate active admission
    if (empty($errors) && $data['patient_id'] > 0) {
        if (!Patient::getById($data['patient_id']) || !in_array($data['doctor_id'], array_map(static fn($doctor) => (int)$doctor['doctor_id'], Doctor::getActiveDoctors()), true)) {
            $errors[] = 'Select an active patient and doctor.';
        } elseif (Admission::hasActiveAdmission($data['patient_id'])) {
            $errors[] = 'This patient already has an active admission. Discharge the current admission before admitting again.';
        }
    }

    if (empty($errors)) {
        try {
            Admission::create($data);
            header('Location: index.php?page=admissions&success=1');
            exit;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$patients = Patient::getActivePatients();
$doctors  = Doctor::getActiveDoctors();
$rooms    = Room::getAvailableRooms();
$admissions = Admission::getAll();

require_once __DIR__ . '/../views/admissions.php';