<?php
require_once __DIR__ . '/../config/database.php';

class Bill
{
    public static function getAdmissionsForBilling(): array
    {
        $pdo = getDBConnection();
        return $pdo->query(
            "SELECT a.admission_id, a.admission_date, a.status AS admission_status,
                    p.first_name, p.last_name, p.patient_code,
                    r.room_number,
                    b.bill_id, b.total_amount, b.status AS bill_status
             FROM admissions a
             JOIN patients p ON a.patient_id = p.patient_id
             JOIN rooms r ON a.room_id = r.room_id
             LEFT JOIN bills b ON b.admission_id = a.admission_id
             ORDER BY a.admission_id DESC"
        )->fetchAll();
    }

    public static function getByAdmission(int $admissionId): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM bills WHERE admission_id = :aid LIMIT 1");
        $stmt->execute(['aid' => $admissionId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getById(int $billId): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT b.*, a.admission_date, a.status AS admission_status,
                    p.first_name, p.last_name, p.patient_code,
                    d.doctor_name, r.room_number, r.bed_number, r.room_rate
             FROM bills b
             JOIN admissions a ON b.admission_id = a.admission_id
             JOIN patients p ON a.patient_id = p.patient_id
             JOIN doctors d ON a.doctor_id = d.doctor_id
             JOIN rooms r ON a.room_id = r.room_id
             WHERE b.bill_id = :bid"
        );
        $stmt->execute(['bid' => $billId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getItems(int $billId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id = :bid ORDER BY bill_item_id");
        $stmt->execute(['bid' => $billId]);
        return $stmt->fetchAll();
    }

    public static function generate(int $admissionId): int
    {
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                "INSERT INTO bills (admission_id, bill_date, total_amount, status)
                 VALUES (:aid, CURDATE(), 0, 'Unpaid')"
            )->execute(['aid' => $admissionId]);
            $billId = (int)$pdo->lastInsertId();

            // Room charges
            $room = self::roomChargeRow($pdo, $admissionId);
            self::insertItem($pdo, $billId, null, $room['description'], $room['amount']);

            // Medicines ordered during this admission (1 unit per order)
            $meds = $pdo->prepare(
                "SELECT m.medicine_name, m.dosage_form, m.unit_price
                 FROM treatment_medicines tm
                 JOIN treatments t ON tm.treatment_id = t.treatment_id
                 JOIN medicines m ON tm.medicine_id = m.medicine_id
                 WHERE t.admission_id = :aid"
            );
            $meds->execute(['aid' => $admissionId]);
            foreach ($meds->fetchAll() as $m) {
                self::insertItem(
                    $pdo, $billId, null,
                    'Medicine: ' . $m['medicine_name'] . ' (' . $m['dosage_form'] . ') x 1',
                    (float)$m['unit_price']
                );
            }

            self::recalcTotal($pdo, $billId);
            $pdo->commit();
            return $billId;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function addItem(int $billId, ?int $serviceId, string $description, float $amount): void
    {
        $pdo = getDBConnection();
        self::insertItem($pdo, $billId, $serviceId, $description, $amount);
        self::recalcTotal($pdo, $billId);
    }

    public static function removeItem(int $billId, int $itemId): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("DELETE FROM bill_items WHERE bill_item_id = :iid AND bill_id = :bid")
            ->execute(['iid' => $itemId, 'bid' => $billId]);
        self::recalcTotal($pdo, $billId);
    }

    public static function refreshRoomCharge(int $billId): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT admission_id FROM bills WHERE bill_id = :bid");
        $stmt->execute(['bid' => $billId]);
        $admissionId = (int)$stmt->fetchColumn();
        if ($admissionId <= 0) {
            return;
        }

        $room = self::roomChargeRow($pdo, $admissionId);
        $find = $pdo->prepare(
            "SELECT bill_item_id FROM bill_items WHERE bill_id = :bid AND description LIKE 'Room Charges:%' LIMIT 1"
        );
        $find->execute(['bid' => $billId]);
        $itemId = $find->fetchColumn();

        if ($itemId) {
            $pdo->prepare("UPDATE bill_items SET description = :d, amount = :a WHERE bill_item_id = :iid")
                ->execute(['d' => $room['description'], 'a' => $room['amount'], 'iid' => $itemId]);
        } else {
            self::insertItem($pdo, $billId, null, $room['description'], $room['amount']);
        }
        self::recalcTotal($pdo, $billId);
    }

    // ---------- helpers (they reuse one connection so transactions stay consistent) ----------

    private static function insertItem(PDO $pdo, int $billId, ?int $serviceId, string $description, float $amount): void
    {
        $pdo->prepare(
            "INSERT INTO bill_items (bill_id, service_id, description, amount)
             VALUES (:bid, :sid, :d, :a)"
        )->execute([
            'bid' => $billId,
            'sid' => $serviceId,
            'd'   => $description,
            'a'   => round($amount, 2),
        ]);
    }

    private static function recalcTotal(PDO $pdo, int $billId): void
    {
        $sum = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM bill_items WHERE bill_id = :bid");
        $sum->execute(['bid' => $billId]);
        $total = $sum->fetchColumn();

        $pdo->prepare("UPDATE bills SET total_amount = :t WHERE bill_id = :bid")
            ->execute(['t' => $total, 'bid' => $billId]);
    }

    private static function stayDays(string $from, ?string $to): int
    {
        $start = new DateTime($from);
        $end = $to ? new DateTime($to) : new DateTime('today');
        if ($end <= $start) {
            return 1;
        }
        return max(1, (int)$start->diff($end)->days);
    }

    private static function roomChargeRow(PDO $pdo, int $admissionId): array
    {
        $stmt = $pdo->prepare(
            "SELECT a.admission_date, r.room_number, r.room_rate,
                    (SELECT MAX(discharge_date) FROM discharges WHERE admission_id = a.admission_id) AS discharge_date
             FROM admissions a
             JOIN rooms r ON a.room_id = r.room_id
             WHERE a.admission_id = :aid"
        );
        $stmt->execute(['aid' => $admissionId]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new Exception('Admission not found.');
        }

        $days = self::stayDays($row['admission_date'], $row['discharge_date']);
        $rate = (float)$row['room_rate'];

        return [
            'description' => 'Room Charges: Room ' . $row['room_number'] . ' (' . $days . ' day' . ($days !== 1 ? 's' : '') . ' x ' . number_format($rate, 2) . ')',
            'amount'      => $days * $rate,
        ];
    }
}