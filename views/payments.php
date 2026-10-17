<?php $peso = fn($n) => '₱' . number_format((float)$n, 2); ?>

<h1>Payment Processing</h1>

<?php if (isset($_GET['paid'])): ?>
    <p style="color:green;">
        Payment recorded successfully.
        <?php $chg = (float)($_GET['change'] ?? 0); if ($chg > 0): ?>
            <strong>Change to give: <?= $peso($chg) ?></strong>
        <?php endif; ?>
    </p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (!$bill): ?>

    <p>Select a bill to record a payment, or print its statement and receipts.</p>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
        <thead>
            <tr><th>Bill</th><th>Admission</th><th>Patient</th><th>Bill Date</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if (empty($bills)): ?>
                <tr><td colspan="9">No bills yet. Generate one on the Billing page first.</td></tr>
            <?php else: foreach ($bills as $b):
                $bBalance = max(0, round((float)$b['total_amount'] - (float)$b['total_paid'], 2));
            ?>
                <tr>
                    <td>#<?= $b['bill_id'] ?></td>
                    <td>ADM-<?= str_pad($b['admission_id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?> (<?= htmlspecialchars($b['patient_code']) ?>)</td>
                    <td><?= htmlspecialchars($b['bill_date']) ?></td>
                    <td><?= $peso($b['total_amount']) ?></td>
                    <td><?= $peso($b['total_paid']) ?></td>
                    <td><?= $peso($bBalance) ?></td>
                    <td><?= htmlspecialchars($b['status']) ?></td>
                    <td>
                        <a href="index.php?page=payments&bill_id=<?= $b['bill_id'] ?>">
                            <?= $b['status'] === 'Paid' ? 'View' : 'Record Payment' ?>
                        </a>
                        | <a href="index.php?page=statement&bill_id=<?= $b['bill_id'] ?>">Statement</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

<?php else:
    $total   = (float)$bill['total_amount'];
    $paid    = (float)$bill['total_paid'];
    $balance = max(0, round($total - $paid, 2));
    $change  = max(0, round($paid - $total, 2));
?>

    <p><a href="index.php?page=payments">&larr; Back to Payments</a> |
       <a href="index.php?page=billing&bill_id=<?= $bill['bill_id'] ?>">View Bill</a> |
       <a href="index.php?page=statement&bill_id=<?= $bill['bill_id'] ?>">Print Statement</a></p>

    <h2>Bill #<?= $bill['bill_id'] ?> &mdash; ADM-<?= str_pad($bill['admission_id'], 5, '0', STR_PAD_LEFT) ?></h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Patient</th><td><?= htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']) ?> (<?= htmlspecialchars($bill['patient_code']) ?>)</td></tr>
        <tr><th>Total Bill</th><td><?= $peso($total) ?></td></tr>
        <tr><th>Total Paid</th><td><?= $peso($paid) ?></td></tr>
        <tr>
            <th>Balance / Change</th>
            <td>
                <?php if ($balance > 0): ?>
                    Balance: <?= $peso($balance) ?>
                <?php elseif ($change > 0): ?>
                    Change: <?= $peso($change) ?>
                <?php else: ?>
                    <?= $peso(0) ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr><th>Status</th><td><?= htmlspecialchars($bill['status']) ?></td></tr>
    </table>

    <h3>Payment History</h3>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
        <thead><tr><th>#</th><th>Date</th><th>Method</th><th>Amount Paid</th><th>Receipt</th></tr></thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="5">No payments yet.</td></tr>
            <?php else: foreach ($payments as $i => $py): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($py['payment_date']) ?></td>
                    <td><?= htmlspecialchars($py['payment_method']) ?></td>
                    <td style="text-align:right;"><?= $peso($py['amount_paid']) ?></td>
                    <td><a href="index.php?page=receipt&payment_id=<?= $py['payment_id'] ?>">OR-<?= str_pad($py['payment_id'], 5, '0', STR_PAD_LEFT) ?></a></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <?php if ($total > 0 && $balance > 0): ?>
        <hr>
        <h3>Record Payment</h3>
        <form method="post" action="index.php?page=payments">
            <input type="hidden" name="action" value="pay">
            <input type="hidden" name="bill_id" value="<?= $bill['bill_id'] ?>">

            <label>Payment Method<br>
                <select name="payment_method" required>
                    <option value="">-- Select --</option>
                    <?php foreach (Payment::METHODS as $m): ?>
                        <option value="<?= $m ?>"><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </label><br>

            <label>Amount Paid<br>
                <input type="text" name="amount" id="amount" autocomplete="off" required>
            </label>
            <button type="button" onclick="document.getElementById('amount').value='<?= number_format($balance, 2, '.', '') ?>'; document.getElementById('amount').dispatchEvent(new Event('input'));">Pay full balance</button>
            <br>
            <strong id="preview"></strong><br><br>

            <button type="submit">RECORD PAYMENT</button>
        </form>

        <script>
        (function () {
            var balance = <?= json_encode($balance) ?>;
            var input = document.getElementById('amount');
            var out = document.getElementById('preview');
            function fmt(n) {
                return '₱' + n.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            input.addEventListener('input', function () {
                var v = parseFloat(input.value);
                if (isNaN(v) || v <= 0) { out.textContent = ''; return; }
                var diff = Math.round((balance - v) * 100) / 100;
                out.textContent = diff >= 0
                    ? 'Balance after payment: ' + fmt(diff)
                    : 'Change to give: ' + fmt(-diff);
            });
        })();
        </script>
    <?php elseif ($total > 0): ?>
        <p><strong>This bill is fully paid.</strong></p>
    <?php else: ?>
        <p>This bill has no charges yet, so there is nothing to pay.</p>
    <?php endif; ?>

<?php endif; ?>