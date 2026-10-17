<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Payment.php';

class Statement
{
    public static function build(int $billId): ?array
    {
        $bill = Payment::getBill($billId);
        if (!$bill) {
            return null;
        }

        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT bi.*, s.category AS service_category
             FROM bill_items bi
             LEFT JOIN services s ON bi.service_id = s.service_id
             WHERE bi.bill_id = :bid
             ORDER BY bi.bill_item_id"
        );
        $stmt->execute(['bid' => $billId]);
        $items = $stmt->fetchAll();

        $groups = [
            'Room Charges'             => 0.0,
            'Medical Services'         => 0.0,
            'Laboratory & Diagnostics' => 0.0,
            'Medicine'                 => 0.0,
            'Other Services'           => 0.0,
        ];

        foreach ($items as &$it) {
            $group = self::groupFor($it);
            $it['group'] = $group;
            $groups[$group] += (float)$it['amount'];
        }
        unset($it);

        return ['bill' => $bill, 'items' => $items, 'groups' => $groups];
    }

    public static function receipt(int $paymentId): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = :pid");
        $stmt->execute(['pid' => $paymentId]);
        $payment = $stmt->fetch();
        if (!$payment) {
            return null;
        }

        $bill = Payment::getBill((int)$payment['bill_id']);
        if (!$bill) {
            return null;
        }

        $before = $pdo->prepare(
            "SELECT COALESCE(SUM(amount_paid), 0) FROM payments WHERE bill_id = :bid AND payment_id < :pid"
        );
        $before->execute(['bid' => $payment['bill_id'], 'pid' => $paymentId]);
        $paidBefore = (float)$before->fetchColumn();

        $total         = (float)$bill['total_amount'];
        $amount        = (float)$payment['amount_paid'];
        $balanceBefore = max(0, round($total - $paidBefore, 2));
        $change        = max(0, round($amount - $balanceBefore, 2));
        $balanceAfter  = max(0, round($balanceBefore - $amount, 2));

        return [
            'payment'        => $payment,
            'bill'           => $bill,
            'balance_before' => $balanceBefore,
            'change'         => $change,
            'balance_after'  => $balanceAfter,
        ];
    }

    private static function groupFor(array $item): string
    {
        $desc = (string)$item['description'];
        $cat  = $item['service_category'] ?? null;

        if (strpos($desc, 'Room Charges:') === 0 || $cat === 'Accommodation') {
            return 'Room Charges';
        }
        if ($cat === 'Professional') {
            return 'Medical Services';
        }
        if ($cat === 'Diagnostic') {
            return 'Laboratory & Diagnostics';
        }
        if (strpos($desc, 'Medicine:') === 0 || $cat === 'Pharmacy') {
            return 'Medicine';
        }
        return 'Other Services';
    }
}