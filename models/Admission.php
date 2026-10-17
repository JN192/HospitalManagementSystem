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
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO admissions 
                 (patient_id, doctor_id, room_id, admission_date, admission_time, admission_type, reason, status)
                 VALUES (:patient_id, :doctor_id, :room_id, :admission_date, :admission_time, :admission_type, :reason, 'Admitted')"
            );
            $stmt->execute($data);

            $pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE room_id = :room_id")
                ->execute(['room_id' => $data['room_id']]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
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