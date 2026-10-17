<?php
require_once __DIR__ . '/../models/Payment.php';

$errors = [];
$billId = (int)($_GET['bill_id'] ?? $_POST['bill_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pay' && $billId > 0) {
    $method    = $_POST['payment_method'] ?? '';
    $amountRaw = trim($_POST['amount'] ?? '');

    if ($amountRaw === '' || !is_numeric($amountRaw)) {
        $errors[] = 'Enter a valid payment amount.';
    } else {
        try {
            $change = Payment::record($billId, $method, (float)$amountRaw);
            header('Location: index.php?page=payments&bill_id=' . $billId . '&paid=1&change=' . number_format($change, 2, '.', ''));
            exit;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$bill = null;
$payments = [];
if ($billId > 0) {
    Payment::syncStatusFor($billId);
    $bill = Payment::getBill($billId);
    if ($bill) {
        $payments = Payment::getPayments($billId);
    } else {
        $errors[] = 'Bill not found.';
    }
}
$bills = $bill ? [] : Payment::getBillsForPayment();

require_once __DIR__ . '/../views/payments.php';