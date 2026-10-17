<?php
require_once __DIR__ . '/../models/Statement.php';

$paymentId = (int)($_GET['payment_id'] ?? 0);
$data = $paymentId > 0 ? Statement::receipt($paymentId) : null;

require_once __DIR__ . '/../views/receipt.php';