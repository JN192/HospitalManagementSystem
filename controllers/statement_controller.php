<?php
require_once __DIR__ . '/../models/Statement.php';
require_once __DIR__ . '/../models/Payment.php';

$billId = (int)($_GET['bill_id'] ?? 0);

$data = null;
$payments = [];
if ($billId > 0) {
    Payment::syncStatusFor($billId);
    $data = Statement::build($billId);
    if ($data) {
        $payments = Payment::getPayments($billId);
    }
}

require_once __DIR__ . '/../views/statement.php';