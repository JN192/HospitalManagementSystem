<?php
require_once __DIR__ . '/../config/database.php';

class Treatment
{
    public static function create(array $data): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO treatments 
             (admission_id, doctor_id, treatment_date, diagnosis, symptoms, follow_up_instructions, treatment_description, notes)
             VALUES (:admission_id, :doctor_id, :treatment_date, :diagnosis, :symptoms, :follow_up_instructions, :treatment_description, :notes)"
        );
        $stmt->execute($data);
    }

    public static function getAll(): array
    {
        $pdo = getDBConnection();
        return $pdo->query(
            "SELECT t.*, p.first_name, p.last_name, p.patient_code, d.doctor_name
             FROM treatments t
             JOIN admissions a ON t.admission_id = a.admission_id
             JOIN patients p ON a.patient_id = p.patient_id
             JOIN doctors d ON t.doctor_id = d.doctor_id
             ORDER BY t.treatment_id DESC"
        )->fetchAll();
    }

    public static function addMedicineOrder(array $data): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO treatment_medicines (treatment_id, medicine_id, dosage, frequency, duration, instructions)
             VALUES (:treatment_id, :medicine_id, :dosage, :frequency, :duration, :instructions)"
        );
        $stmt->execute($data);
    }

    public static function getMedicineOrders(int $treatmentId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT tm.*, m.medicine_name, m.dosage_form
             FROM treatment_medicines tm
             JOIN medicines m ON tm.medicine_id = m.medicine_id
             WHERE tm.treatment_id = :id"
        );
        $stmt->execute(['id' => $treatmentId]);
        return $stmt->fetchAll();
    }
}