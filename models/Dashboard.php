<?php
require_once __DIR__ . '/../config/database.php';

class Dashboard
{
    public static function getSummary(): array
    {
        $pdo = getDBConnection();
        $today = date('Y-m-d');
        $stmt = $pdo->prepare(
            "SELECT
                (SELECT COUNT(*) FROM patients) AS total_patients,
                (SELECT COUNT(DISTINCT patient_id) FROM admissions WHERE status = 'Admitted') AS admitted_patients,
                (SELECT COUNT(*) FROM rooms WHERE status = 'Available') AS available_beds,
                (SELECT COUNT(*) FROM rooms WHERE status = 'Occupied') AS occupied_beds,
                (SELECT COUNT(*) FROM admissions WHERE admission_date = :admission_date) AS todays_admissions,
                (SELECT COUNT(*) FROM discharges WHERE discharge_date = :discharge_date) AS todays_discharges,
                (SELECT COALESCE(SUM(GREATEST(b.total_amount - COALESCE(
                    (SELECT SUM(p.amount_paid) FROM payments p WHERE p.bill_id = b.bill_id), 0
                ), 0)), 0) FROM bills b) AS outstanding_bills,
                (SELECT COUNT(*) FROM admissions
                 WHERE admission_date = :emergency_date AND admission_type = 'Emergency') AS emergency_admissions,
                (SELECT COUNT(*) FROM admissions
                 WHERE admission_date = :regular_date AND admission_type = 'Regular') AS regular_admissions"
        );
        $stmt->execute([
            'admission_date' => $today,
            'discharge_date' => $today,
            'emergency_date' => $today,
            'regular_date' => $today,
        ]);

        $summary = $stmt->fetch();
        if ($summary === false) {
            throw new RuntimeException('Unable to load the hospital dashboard summary.');
        }

        return $summary;
    }
}
