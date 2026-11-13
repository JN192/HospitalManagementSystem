<?php
require_once __DIR__ . '/../config/database.php';

class Discharge
{
    public static function getActiveAdmissions(): array
    {
        return getDBConnection()->query(
            "SELECT a.admission_id, a.patient_id, a.room_id, a.admission_date,
                    p.patient_code, p.first_name, p.last_name, d.doctor_name,
                    r.room_number, r.bed_number,
                    b.bill_id, b.total_amount,
                    COALESCE((SELECT SUM(py.amount_paid) FROM payments py WHERE py.bill_id = b.bill_id), 0) AS total_paid
             FROM admissions a
             JOIN patients p ON p.patient_id = a.patient_id
             JOIN doctors d ON d.doctor_id = a.doctor_id
             JOIN rooms r ON r.room_id = a.room_id
             LEFT JOIN bills b ON b.admission_id = a.admission_id
             WHERE a.status = 'Admitted'
             ORDER BY a.admission_date, a.admission_id"
        )->fetchAll();
    }

    public static function create(int $admissionId, array $data): void
    {
        if ($admissionId <= 0 || trim($data['discharge_diagnosis'] ?? '') === '' || trim($data['discharge_instructions'] ?? '') === '') {
            throw new RuntimeException('An active admission, discharge diagnosis, and discharge instructions are required.');
        }
        $date = DateTime::createFromFormat('!Y-m-d', $data['discharge_date'] ?? '');
        if (!$date || $date->format('Y-m-d') !== $data['discharge_date'] || $data['discharge_date'] > date('Y-m-d')) {
            throw new RuntimeException('Enter a valid discharge date that is not in the future.');
        }
        if (($data['follow_up_date'] ?? '') !== '') {
            $followUp = DateTime::createFromFormat('!Y-m-d', $data['follow_up_date']);
            if (!$followUp || $followUp->format('Y-m-d') !== $data['follow_up_date'] || $followUp < $date) {
                throw new RuntimeException('Follow-up date must be valid and on or after discharge.');
            }
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT room_id, admission_date, status FROM admissions WHERE admission_id = :id FOR UPDATE'
            );
            $stmt->execute(['id' => $admissionId]);
            $admission = $stmt->fetch();
            if (!$admission || $admission['status'] !== 'Admitted') {
                throw new RuntimeException('This admission is not active or has already been discharged.');
            }
            if ($data['discharge_date'] < $admission['admission_date'] || $data['discharge_date'] > date('Y-m-d')) {
                throw new RuntimeException('Discharge date must be between the admission date and today.');
            }

            $insert = $pdo->prepare(
                'INSERT INTO discharges
                    (admission_id, discharge_date, discharge_diagnosis, discharge_instructions, follow_up_date)
                 VALUES
                    (:admission_id, :discharge_date, :discharge_diagnosis, :discharge_instructions, :follow_up_date)'
            );
            $insert->execute([
                'admission_id' => $admissionId,
                'discharge_date' => $data['discharge_date'],
                'discharge_diagnosis' => $data['discharge_diagnosis'],
                'discharge_instructions' => $data['discharge_instructions'],
                'follow_up_date' => $data['follow_up_date'] !== '' ? $data['follow_up_date'] : null,
            ]);

            $update = $pdo->prepare(
                "UPDATE admissions SET status = 'Discharged'
                 WHERE admission_id = :admission_id AND status = 'Admitted'"
            );
            $update->execute(['admission_id' => $admissionId]);
            if ($update->rowCount() !== 1) {
                throw new RuntimeException('The admission status changed before discharge could be completed.');
            }

            $room = $pdo->prepare(
                "UPDATE rooms SET status = 'Available'
                 WHERE room_id = :room_id AND status = 'Occupied'
                   AND NOT EXISTS (
                       SELECT 1 FROM admissions
                       WHERE room_id = :check_room_id AND status = 'Admitted'
                   )"
            );
            $room->execute([
                'room_id' => $admission['room_id'],
                'check_room_id' => $admission['room_id'],
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
