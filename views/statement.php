<?php $peso = fn($n) => '₱' . number_format((float)$n, 2); ?>

<style>
    .doc { border-collapse: collapse; width: 100%; max-width: 560px; }
    .doc td { padding: 4px 8px; }
    .doc td.amt { text-align: right; white-space: nowrap; }
    .doc tr.line td { border-top: 2px solid #000; font-weight: bold; }
    .doc-detail { border-collapse: collapse; width: 100%; max-width: 760px; }
    .doc-detail th, .doc-detail td { border: 1px solid #444; padding: 5px 8px; }
    @media print {
        .sidebar, .no-print { display: none !important; }
        .app-shell { display: block !important; }
        .content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    }
</style>

<?php if (!$data): ?>

    <h1>Billing Statement</h1>
    <p>Bill not found.</p>
    <p><a href="index.php?page=payments">&larr; Back to Payments</a></p>

<?php else:
    $bill    = $data['bill'];
    $groups  = $data['groups'];
    $items   = $data['items'];
    $total   = (float)$bill['total_amount'];
    $paid    = (float)$bill['total_paid'];
    $balance = max(0, round($total - $paid, 2));
    $change  = max(0, round($paid - $total, 2));
?>

    <div class="no-print">
        <a href="index.php?page=payments&bill_id=<?= $bill['bill_id'] ?>">&larr; Back to Payment</a>
        <button type="button" onclick="window.print()">PRINT STATEMENT</button>
        <hr>
    </div>

    <h2>HOSPITAL BILLING STATEMENT</h2>
    <p>
        <strong>Patient:</strong> <?= htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']) ?> (<?= htmlspecialchars($bill['patient_code']) ?>)<br>
        <strong>Admission:</strong> ADM-<?= str_pad($bill['admission_id'], 5, '0', STR_PAD_LEFT) ?><br>
        <strong>Bill No.:</strong> #<?= $bill['bill_id'] ?><br>
        <strong>Bill Date:</strong> <?= htmlspecialchars($bill['bill_date']) ?><br>
        <strong>Status:</strong> <?= htmlspecialchars($bill['status']) ?>
    </p>

    <table class="doc">
        <?php foreach ($groups as $label => $amount): ?>
            <tr>
                <td><?= htmlspecialchars($label) ?></td>
                <td class="amt"><?= $peso($amount) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="line"><td>TOTAL</td><td class="amt"><?= $peso($total) ?></td></tr>
        <tr><td>PAID</td><td class="amt"><?= $peso($paid) ?></td></tr>
        <tr>
            <td>BALANCE/CHANGE</td>
            <td class="amt">
                <?= $peso($balance > 0 ? $balance : $change) ?>
                <small>(<?= $balance > 0 ? 'balance due' : ($change > 0 ? 'change' : 'settled') ?>)</small>
            </td>
        </tr>
    </table>

    <h4>Itemized Charges</h4>
    <table class="doc-detail">
        <thead><tr><th>Description</th><th>Category</th><th>Amount</th></tr></thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="3">No charges on this bill.</td></tr>
            <?php else: foreach ($items as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['description']) ?></td>
                    <td><?= htmlspecialchars($it['group']) ?></td>
                    <td style="text-align:right;"><?= $peso($it['amount']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <h4>Payments</h4>
    <table class="doc-detail">
        <thead><tr><th>Date</th><th>Method</th><th>Amount Paid</th></tr></thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="3">No payments recorded.</td></tr>
            <?php else: foreach ($payments as $py): ?>
                <tr>
                    <td><?= htmlspecialchars($py['payment_date']) ?></td>
                    <td><?= htmlspecialchars($py['payment_method']) ?></td>
                    <td style="text-align:right;"><?= $peso($py['amount_paid']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

<?php endif; ?>