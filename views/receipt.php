<?php $peso = fn($n) => '₱' . number_format((float)$n, 2); ?>

<style>
    .doc { border-collapse: collapse; width: 100%; max-width: 460px; }
    .doc td { padding: 4px 8px; }
    .doc td.amt { text-align: right; white-space: nowrap; }
    .doc tr.line td { border-top: 2px solid #000; font-weight: bold; }
    @media print {
        .sidebar, .no-print { display: none !important; }
        .app-shell { display: block !important; }
        .content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    }
</style>

<?php if (!$data): ?>

    <h1>Payment Receipt</h1>
    <p>Payment not found.</p>
    <p><a href="index.php?page=payments">&larr; Back to Payments</a></p>

<?php else:
    $pay  = $data['payment'];
    $bill = $data['bill'];
?>

    <div class="no-print">
        <a href="index.php?page=payments&bill_id=<?= $bill['bill_id'] ?>">&larr; Back to Payment</a>
        <button type="button" onclick="window.print()">PRINT RECEIPT</button>
        <hr>
    </div>

    <h2>HOSPITAL PAYMENT RECEIPT</h2>
    <p>
        <strong>Receipt No.:</strong> OR-<?= str_pad($pay['payment_id'], 5, '0', STR_PAD_LEFT) ?><br>
        <strong>Date:</strong> <?= htmlspecialchars($pay['payment_date']) ?><br>
        <strong>Patient:</strong> <?= htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']) ?> (<?= htmlspecialchars($bill['patient_code']) ?>)<br>
        <strong>Admission:</strong> ADM-<?= str_pad($bill['admission_id'], 5, '0', STR_PAD_LEFT) ?><br>
        <strong>Bill No.:</strong> #<?= $bill['bill_id'] ?><br>
        <strong>Payment Method:</strong> <?= htmlspecialchars($pay['payment_method']) ?>
    </p>

    <table class="doc">
        <tr><td>Total Bill</td><td class="amt"><?= $peso($bill['total_amount']) ?></td></tr>
        <tr><td>Balance before this payment</td><td class="amt"><?= $peso($data['balance_before']) ?></td></tr>
        <tr class="line"><td>AMOUNT RECEIVED</td><td class="amt"><?= $peso($pay['amount_paid']) ?></td></tr>
        <tr><td>Change</td><td class="amt"><?= $peso($data['change']) ?></td></tr>
        <tr><td>Remaining balance</td><td class="amt"><?= $peso($data['balance_after']) ?></td></tr>
    </table>

    <p><?= $data['balance_after'] <= 0 ? '<strong>PAID IN FULL</strong>' : 'Partial payment. Please settle the remaining balance.' ?></p>
    <br>
    <p>Received by: ______________________</p>

<?php endif; ?>