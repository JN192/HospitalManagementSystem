<?php
require_once __DIR__ . '/../config/database.php';

class Treatment
{
    public static function create(array $data): void
    {
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            $admission = $pdo->prepare(
                "SELECT admission_date FROM admissions WHERE admission_id = :id AND status = 'Admitted' FOR UPDATE"
            );
            $admission->execute(['id' => $data['admission_id']]);
            $admissionDate = $admission->fetchColumn();
            if (!$admissionDate) {
                throw new RuntimeException('Treatment requires a valid active admission.');
            }

            $doctor = $pdo->prepare("SELECT status FROM doctors WHERE doctor_id = :id");
            $doctor->execute(['id' => $data['doctor_id']]);
            if ($doctor->fetchColumn() !== 'Active') {
                throw new RuntimeException('Select an active attending doctor.');
            }

            $date = DateTime::createFromFormat('!Y-m-d', $data['treatment_date']);
            if (!$date || $date->format('Y-m-d') !== $data['treatment_date'] || $data['treatment_date'] < $admissionDate || $data['treatment_date'] > date('Y-m-d')) {
                throw new RuntimeException('Treatment date must be between the admission date and today.');
            }
            if (trim($data['diagnosis']) === '' || trim($data['treatment_description']) === '') {
                throw new RuntimeException('Diagnosis and treatment description are required.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO treatments
                 (admission_id, doctor_id, treatment_date, diagnosis, symptoms, follow_up_instructions, treatment_description, notes)
                 VALUES (:admission_id, :doctor_id, :treatment_date, :diagnosis, :symptoms, :follow_up_instructions, :treatment_description, :notes)"
            );
            $stmt->execute($data);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
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