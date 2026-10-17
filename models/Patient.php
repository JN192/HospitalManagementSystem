<?php
require_once __DIR__ . '/../config/database.php';

class Patient
{
    public static function getAll(string $search = ''): array
    {
        $pdo = getDBConnection();
        if ($search !== '') {
            $stmt = $pdo->prepare(
                "SELECT * FROM patients 
                 WHERE first_name LIKE :s OR last_name LIKE :s OR patient_code LIKE :s
                 ORDER BY patient_id DESC"
            );
            $stmt->execute(['s' => "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM patients ORDER BY patient_id DESC");
        }
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO patients 
             (patient_code, first_name, last_name, birth_date, sex, contact_number, address, emergency_contact, status)
             VALUES (:patient_code, :first_name, :last_name, :birth_date, :sex, :contact_number, :address, :emergency_contact, 'Active')"
        );
        $stmt->execute($data);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = getDBConnection();
        $data['id'] = $id;
        $stmt = $pdo->prepare(
            "UPDATE patients SET 
                patient_code = :patient_code, first_name = :first_name, last_name = :last_name,
                birth_date = :birth_date, sex = :sex, contact_number = :contact_number,
                address = :address, emergency_contact = :emergency_contact
             WHERE patient_id = :id"
        );
        $stmt->execute($data);
    }

    public static function deactivate(int $id): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("UPDATE patients SET status = 'Inactive' WHERE patient_id = :id")->execute(['id' => $id]);
    }

    public static function activate(int $id): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("UPDATE patients SET status = 'Active' WHERE patient_id = :id")->execute(['id' => $id]);
    }

    public static function getAdmissionHistory(int $patientId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT a.*, d.doctor_name, r.room_number, r.room_type
             FROM admissions a
             JOIN doctors d ON a.doctor_id = d.doctor_id
             JOIN rooms r ON a.room_id = r.room_id
             WHERE a.patient_id = :id
             ORDER BY a.admission_date DESC"
        );
        $stmt->execute(['id' => $patientId]);
        return $stmt->fetchAll();
    }

    public static function getTreatmentHistory(int $patientId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT t.*, d.doctor_name
             FROM treatments t
             JOIN admissions a ON t.admission_id = a.admission_id
             JOIN doctors d ON t.doctor_id = d.doctor_id
             WHERE a.patient_id = :id
             ORDER BY t.treatment_date DESC, t.treatment_id DESC"
        );
        $stmt->execute(['id' => $patientId]);
        $treatments = $stmt->fetchAll();

        $medStmt = $pdo->prepare(
            "SELECT tm.*, m.medicine_name, m.dosage_form
             FROM treatment_medicines tm
             JOIN medicines m ON tm.medicine_id = m.medicine_id
             WHERE tm.treatment_id = :tid"
        );
        foreach ($treatments as &$t) {
            $medStmt->execute(['tid' => $t['treatment_id']]);
            $t['medicines'] = $medStmt->fetchAll();
        }
        unset($t);

        return $treatments;
    }

    public static function getBillingHistory(int $patientId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT b.*
             FROM bills b
             JOIN admissions a ON b.admission_id = a.admission_id
             WHERE a.patient_id = :id
             ORDER BY b.bill_date DESC"
        );
        $stmt->execute(['id' => $patientId]);
        return $stmt->fetchAll();
    }

    public static function getDischargeHistory(int $patientId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT disc.*
             FROM discharges disc
             JOIN admissions a ON disc.admission_id = a.admission_id
             WHERE a.patient_id = :id
             ORDER BY disc.discharge_date DESC"
        );
        $stmt->execute(['id' => $patientId]);
        return $stmt->fetchAll();
    }

    public static function getActivePatients(): array
    {
        $pdo = getDBConnection();
        return $pdo->query("SELECT * FROM patients WHERE status = 'Active' ORDER BY first_name")->fetchAll();
    }
}