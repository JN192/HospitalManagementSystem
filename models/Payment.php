<?php
require_once __DIR__ . '/../config/database.php';

class Payment
{
    public const METHODS = ['Cash', 'Card', 'E-Wallet', 'Insurance', 'Other'];

    public static function getBillsForPayment(): array
    {
        $pdo = getDBConnection();
        return $pdo->query(
            "SELECT b.bill_id, b.bill_date, b.total_amount, b.status, b.admission_id,
                    p.first_name, p.last_name, p.patient_code,
                    COALESCE((SELECT SUM(py.amount_paid) FROM payments py WHERE py.bill_id = b.bill_id), 0) AS total_paid
             FROM bills b
             JOIN admissions a ON b.admission_id = a.admission_id
             JOIN patients p ON a.patient_id = p.patient_id
             ORDER BY b.bill_id DESC"
        )->fetchAll();
    }

    public static function getBill(int $billId): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT b.*, p.first_name, p.last_name, p.patient_code,
                    COALESCE((SELECT SUM(py.amount_paid) FROM payments py WHERE py.bill_id = b.bill_id), 0) AS total_paid
             FROM bills b
             JOIN admissions a ON b.admission_id = a.admission_id
             JOIN patients p ON a.patient_id = p.patient_id
             WHERE b.bill_id = :bid"
        );
        $stmt->execute(['bid' => $billId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getPayments(int $billId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM payments WHERE bill_id = :bid ORDER BY payment_date, payment_id"
        );
        $stmt->execute(['bid' => $billId]);
        return $stmt->fetchAll();
    }

    public static function syncStatusFor(int $billId): void
    {
        $pdo = getDBConnection();
        self::syncStatus($pdo, $billId);
    }

    /**
     * Records a payment and returns the change to give (0 if none).
     * Throws RuntimeException with a readable message when a rule is broken.
     */
    public static function record(int $billId, string $method, float $amount): float
    {
        if (!in_array($method, self::METHODS, true)) {
            throw new RuntimeException('Select a valid payment method.');
        }
        if ($amount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT total_amount FROM bills WHERE bill_id = :bid FOR UPDATE");
            $stmt->execute(['bid' => $billId]);
            $bill = $stmt->fetch();
            if (!$bill) {
                throw new RuntimeException('Bill not found.');
            }

            $total = (float)$bill['total_amount'];
            if ($total <= 0) {
                throw new RuntimeException('This bill has no charges to pay.');
            }

            $paid = self::sumPaid($pdo, $billId);
            $balance = round($total - $paid, 2);
            if ($balance <= 0) {
                throw new RuntimeException('This bill is already fully paid.');
            }

            $amount = round($amount, 2);
            if ($method !== 'Cash' && $amount > $balance) {
                throw new RuntimeException(
                    'Only cash payments can be more than the balance. Enter ' . number_format($balance, 2) . ' or less for ' . $method . '.'
                );
            }

            $pdo->prepare(
                "INSERT INTO payments (bill_id, amount_paid, payment_method, payment_date)
                 VALUES (:bid, :amt, :method, NOW())"
            )->execute(['bid' => $billId, 'amt' => $amount, 'method' => $method]);

            self::syncStatus($pdo, $billId);
            $pdo->commit();

            return max(0, round($amount - $balance, 2));
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // ---------- helpers ----------

    private static function sumPaid(PDO $pdo, int $billId): float
    {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount_paid), 0) FROM payments WHERE bill_id = :bid");
        $stmt->execute(['bid' => $billId]);
        return (float)$stmt->fetchColumn();
    }

    private static function syncStatus(PDO $pdo, int $billId): void
    {
        $stmt = $pdo->prepare("SELECT total_amount FROM bills WHERE bill_id = :bid");
        $stmt->execute(['bid' => $billId]);
        $total = (float)$stmt->fetchColumn();
        $paid = self::sumPaid($pdo, $billId);

        if ($total > 0 && round($total - $paid, 2) <= 0) {
            $status = 'Paid';
        } elseif ($paid > 0) {
            $status = 'Partial';
        } else {
            $status = 'Unpaid';
        }

        $pdo->prepare("UPDATE bills SET status = :s WHERE bill_id = :bid")
            ->execute(['s' => $status, 'bid' => $billId]);
    }
}