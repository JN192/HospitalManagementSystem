<?php
require_once __DIR__ . '/../config/database.php';

class Report
{
    public static function patients(): array
    {
        return self::run(
            "SELECT patient_code, first_name, last_name, sex, contact_number, status
             FROM patients
             ORDER BY last_name, first_name",
            []
        );
    }

    public static function doctors(): array
    {
        return self::run(
            "SELECT d.doctor_name, d.specialization, dp.department_name
             FROM doctors d
             JOIN departments dp ON d.department_id = dp.department_id
             ORDER BY d.doctor_name",
            []
        );
    }

    public static function admissions(?string $from, ?string $to): array
    {
        $params = [];
        $where = self::dateClause('a.admission_date', $from, $to, $params);
        return self::run(
            "SELECT a.admission_id, a.admission_date, a.status,
                    p.first_name, p.last_name, d.doctor_name, r.room_number, r.bed_number
             FROM admissions a
             JOIN patients p ON a.patient_id = p.patient_id
             JOIN doctors d ON a.doctor_id = d.doctor_id
             JOIN rooms r ON a.room_id = r.room_id"
            . $where .
            " ORDER BY a.admission_date DESC, a.admission_id DESC",
            $params
        );
    }

    public static function treatments(?string $from, ?string $to): array
    {
        $params = [];
        $where = self::dateClause('t.treatment_date', $from, $to, $params);
        return self::run(
            "SELECT t.treatment_date, t.diagnosis, t.treatment_description,
                    p.first_name, p.last_name, d.doctor_name
             FROM treatments t
             JOIN admissions a ON t.admission_id = a.admission_id
             JOIN patients p ON a.patient_id = p.patient_id
             JOIN doctors d ON t.doctor_id = d.doctor_id"
            . $where .
            " ORDER BY t.treatment_date DESC, t.treatment_id DESC",
            $params
        );
    }

    public static function billing(?string $from, ?string $to): array
    {
        $params = [];
        $where = self::dateClause('b.bill_date', $from, $to, $params);
        return self::run(
            "SELECT b.bill_id, b.bill_date, b.total_amount, b.status,
                    p.first_name, p.last_name,
                    COALESCE((SELECT SUM(py.amount_paid) FROM payments py WHERE py.bill_id = b.bill_id), 0) AS total_paid
             FROM bills b
             JOIN admissions a ON b.admission_id = a.admission_id
             JOIN patients p ON a.patient_id = p.patient_id"
            . $where .
            " ORDER BY b.bill_date DESC, b.bill_id DESC",
            $params
        );
    }

    public static function discharges(?string $from, ?string $to): array
    {
        $params = [];
        $where = self::dateClause('dc.discharge_date', $from, $to, $params);
        return self::run(
            "SELECT dc.discharge_date, a.admission_date,
                    p.first_name, p.last_name, d.doctor_name
             FROM discharges dc
             JOIN admissions a ON dc.admission_id = a.admission_id
             JOIN patients p ON a.patient_id = p.patient_id
             JOIN doctors d ON a.doctor_id = d.doctor_id"
            . $where .
            " ORDER BY dc.discharge_date DESC, dc.discharge_id DESC",
            $params
        );
    }

    // ---------- helpers ----------

    // $column is always a hard-coded column name from this class, never user input.
    private static function dateClause(string $column, ?string $from, ?string $to, array &$params): string
    {
        $parts = [];
        if ($from !== null) {
            $parts[] = $column . ' >= :from_date';
            $params['from_date'] = $from;
        }
        if ($to !== null) {
            $parts[] = $column . ' <= :to_date';
            $params['to_date'] = $to;
        }
        return $parts ? ' WHERE ' . implode(' AND ', $parts) : '';
    }

    private static function run(string $sql, array $params): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}