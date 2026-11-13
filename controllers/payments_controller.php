<?php
require_once __DIR__ . '/../models/Payment.php';

$errors = [];
$submittedBillId = $_GET['bill_id'] ?? $_POST['bill_id'] ?? null;
$billId = filter_var($submittedBillId, FILTER_VALIDATE_INT);
$billId = $billId !== false && $billId !== null && $billId > 0 ? $billId : 0;
if ($submittedBillId !== null && $billId === 0) {
    $errors[] = 'Choose a valid bill.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pay' && $billId > 0) {
    $submittedMethod = $_POST['payment_method'] ?? '';
    $submittedAmount = $_POST['amount'] ?? '';
    $method = is_string($submittedMethod) ? $submittedMethod : '';
    $amountRaw = is_string($submittedAmount) ? trim($submittedAmount) : '';

    if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $amountRaw) || (float)$amountRaw <= 0) {
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