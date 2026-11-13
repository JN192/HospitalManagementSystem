<?php
require_once __DIR__ . '/../config/database.php';

class Admission
{
    public static function getAll(): array
    {
        $pdo = getDBConnection();
        return $pdo->query(
            "SELECT a.*, p.first_name, p.last_name, d.doctor_name, r.room_number, r.bed_number
             FROM admissions a
             JOIN patients p ON a.patient_id = p.patient_id
             JOIN doctors d ON a.doctor_id = d.doctor_id
             JOIN rooms r ON a.room_id = r.room_id
             ORDER BY a.admission_id DESC"
        )->fetchAll();
    }

    public static function hasActiveAdmission(int $patientId): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM admissions WHERE patient_id = :pid AND status = 'Admitted'"
        );
        $stmt->execute(['pid' => $patientId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(array $data): void
    {
        foreach (['patient_id', 'doctor_id', 'room_id'] as $idField) {
            if (!isset($data[$idField]) || filter_var($data[$idField], FILTER_VALIDATE_INT) === false || (int)$data[$idField] <= 0) {
                throw new RuntimeException('Select a valid patient, doctor, and room/bed.');
            }
        }
        if (!in_array($data['admission_type'] ?? '', ['Emergency', 'Regular'], true)) {
            throw new RuntimeException('Select a valid admission type.');
        }
        $date = DateTime::createFromFormat('!Y-m-d', $data['admission_date'] ?? '');
        if (!$date || $date->format('Y-m-d') !== $data['admission_date'] || $data['admission_date'] > date('Y-m-d') || $data['admission_date'] < date('Y-m-d', strtotime('-30 days'))) {
            throw new RuntimeException('Enter a valid admission date within the past 30 days.');
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $data['admission_time'] ?? '')) {
            throw new RuntimeException('Enter a valid admission time.');
        }
        if (trim((string)($data['reason'] ?? '')) === '') {
            throw new RuntimeException('Reason for admission is required.');
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            $patient = $pdo->prepare('SELECT status FROM patients WHERE patient_id = :id FOR UPDATE');
            $patient->execute(['id' => $data['patient_id']]);
            if ($patient->fetchColumn() !== 'Active') {
                throw new RuntimeException('The selected patient is not active.');
            }

            $doctor = $pdo->prepare("SELECT status FROM doctors WHERE doctor_id = :id");
            $doctor->execute(['id' => $data['doctor_id']]);
            if ($doctor->fetchColumn() !== 'Active') {
                throw new RuntimeException('The selected doctor is not active.');
            }

            $room = $pdo->prepare('SELECT status FROM rooms WHERE room_id = :id FOR UPDATE');
            $room->execute(['id' => $data['room_id']]);
            if ($room->fetchColumn() !== 'Available') {
                throw new RuntimeException('The selected room/bed is no longer available.');
            }

            $activeAdmission = $pdo->prepare(
                "SELECT COUNT(*) FROM admissions WHERE patient_id = :patient_id AND status = 'Admitted'"
            );
            $activeAdmission->execute(['patient_id' => $data['patient_id']]);
            if ((int)$activeAdmission->fetchColumn() > 0) {
                throw new RuntimeException('This patient already has an active admission.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO admissions 
                 (patient_id, doctor_id, room_id, admission_date, admission_time, admission_type, reason, status)
                 VALUES (:patient_id, :doctor_id, :room_id, :admission_date, :admission_time, :admission_type, :reason, 'Admitted')"
            );
            $stmt->execute($data);

            $occupy = $pdo->prepare(
                "UPDATE rooms SET status = 'Occupied' WHERE room_id = :room_id AND status = 'Available'"
            );
            $occupy->execute(['room_id' => $data['room_id']]);
            if ($occupy->rowCount() !== 1) {
                throw new RuntimeException('The selected room/bed is no longer available.');
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function getCurrentlyAdmitted(string $search = ''): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT a.*, p.first_name, p.last_name, p.patient_code, 
                       d.doctor_name, r.room_number, r.bed_number
                FROM admissions a
                JOIN patients p ON a.patient_id = p.patient_id
                JOIN doctors d ON a.doctor_id = d.doctor_id
                JOIN rooms r ON a.room_id = r.room_id
                WHERE a.status = 'Admitted'";
        if ($search !== '') {
            $sql .= " AND (p.first_name LIKE :s OR p.last_name LIKE :s OR p.patient_code LIKE :s)";
            $sql .= " ORDER BY a.admission_date DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['s' => "%$search%"]);
        } else {
            $sql .= " ORDER BY a.admission_date DESC";
            $stmt = $pdo->query($sql);
        }
        return $stmt->fetchAll();
    }

    public static function getByIdFull(int $admissionId): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT a.*, p.first_name, p.last_name, p.patient_code, 
                    d.doctor_name, r.room_number, r.bed_number
             FROM admissions a
             JOIN patients p ON a.patient_id = p.patient_id
             JOIN doctors d ON a.doctor_id = d.doctor_id
             JOIN rooms r ON a.room_id = r.room_id
             WHERE a.admission_id = :id"
        );
        $stmt->execute(['id' => $admissionId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getTreatmentsByAdmission(int $admissionId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT t.*, d.doctor_name FROM treatments t
             JOIN doctors d ON t.doctor_id = d.doctor_id
             WHERE t.admission_id = :id ORDER BY t.treatment_date DESC"
        );
        $stmt->execute(['id' => $admissionId]);
        return $stmt->fetchAll();
    }
}