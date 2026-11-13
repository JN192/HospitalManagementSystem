<?php
$peso = fn($n) => '₱' . number_format((float)$n, 2);
$full = fn($r) => $r['first_name'] . ' ' . $r['last_name'];
$admNo = fn($id) => 'ADM-' . str_pad($id, 5, '0', STR_PAD_LEFT);
?>

<style>
    .report-tabs a { margin-right: 14px; }
    .report-tabs a.current { font-weight: bold; text-decoration: none; color: #000; }
    .report { border-collapse: collapse; width: 100%; }
    .report th, .report td { border: 1px solid #444; padding: 5px 8px; text-align: left; }
    .report td.amt, .report th.amt { text-align: right; white-space: nowrap; }
    .report tr.totals td { font-weight: bold; background: #f0f0f0; }
    @media print {
        .sidebar, .no-print { display: none !important; }
        .app-shell { display: block !important; }
        .content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    }
</style>

<div class="no-print">
    <h1>Hospital Reports</h1>

    <p class="report-tabs">
        <?php foreach ($types as $key => $label): ?>
            <a href="index.php?page=reports&type=<?= $key ?>" class="<?= $key === $type ? 'current' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </p>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($dated): ?>
        <form method="get" action="index.php">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            From <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
            To <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
            <button type="submit">Apply</button>
            <a href="index.php?page=reports&type=<?= $type ?>">Reset</a>
        </form>
    <?php endif; ?>

    <p><button type="button" onclick="window.print()">PRINT REPORT</button></p>
    <hr>
</div>

<h2><?= htmlspecialchars($types[$type]) ?></h2>
<p>
    <?php if ($dated && ($from !== '' || $to !== '')): ?>
        <strong>Period:</strong> <?= $from !== '' ? htmlspecialchars($from) : 'beginning' ?> to <?= $to !== '' ? htmlspecialchars($to) : 'today' ?><br>
    <?php endif; ?>
    <strong>Generated:</strong> <?= date('Y-m-d H:i') ?> &nbsp;|&nbsp;
    <strong>Records:</strong> <?= count($rows) ?>
</p>

<?php if ($type === 'patients'): ?>

    <table class="report">
        <thead><tr><th>Patient ID</th><th>Name</th><th>Sex</th><th>Contact</th><th>Status</th></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5">No records found.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['patient_code']) ?></td>
                    <td><?= htmlspecialchars($full($r)) ?></td>
                    <td><?= htmlspecialchars($r['sex']) ?></td>
                    <td><?= htmlspecialchars($r['contact_number']) ?></td>
                    <td><?= htmlspecialchars($r['status']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

<?php elseif ($type === 'doctors'): ?>

    <table class="report">
        <thead><tr><th>Doctor</th><th>Specialization</th><th>Department</th></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="3">No records found.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($r['specialization']) ?></td>
                    <td><?= htmlspecialchars($r['department_name']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

<?php elseif ($type === 'admissions'): ?>

    <table class="report">
        <thead><tr><th>Admission No.</th><th>Patient</th><th>Doctor</th><th>Room</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6">No records found.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= $admNo($r['admission_id']) ?></td>
                    <td><?= htmlspecialchars($full($r)) ?></td>
                    <td><?= htmlspecialchars($r['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($r['room_number'] . ' / ' . $r['bed_number']) ?></td>
                    <td><?= htmlspecialchars($r['admission_date']) ?></td>
                    <td><?= htmlspecialchars($r['status']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

<?php elseif ($type === 'treatments'): ?>

    <table class="report">
        <thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Treatment</th></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5">No records found.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['treatment_date']) ?></td>
                    <td><?= htmlspecialchars($full($r)) ?></td>
                    <td><?= htmlspecialchars($r['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($r['diagnosis']) ?></td>
                    <td><?= htmlspecialchars($r['treatment_description']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

<?php elseif ($type === 'billing'): ?>

    <?php $sumBill = 0; $sumPaid = 0; $sumBalance = 0; ?>
    <table class="report">
        <thead><tr><th>Date</th><th>Patient</th><th class="amt">Bill</th><th class="amt">Paid</th><th class="amt">Balance</th></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5">No records found.</td></tr>
            <?php else: foreach ($rows as $r):
                $total   = (float)$r['total_amount'];
                $applied = min((float)$r['total_paid'], $total);
                $balance = max(0, round($total - $applied, 2));
                $sumBill += $total; $sumPaid += $applied; $sumBalance += $balance;
            ?>
                <tr>
                    <td><?= htmlspecialchars($r['bill_date']) ?></td>
                    <td><?= htmlspecialchars($full($r)) ?></td>
                    <td class="amt"><?= $peso($total) ?></td>
                    <td class="amt"><?= $peso($applied) ?></td>
                    <td class="amt"><?= $peso($balance) ?></td>
                </tr>
            <?php endforeach; ?>
                <tr class="totals">
                    <td colspan="2">TOTAL</td>
                    <td class="amt"><?= $peso($sumBill) ?></td>
                    <td class="amt"><?= $peso($sumPaid) ?></td>
                    <td class="amt"><?= $peso($sumBalance) ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php elseif ($type === 'discharges'): ?>

    <table class="report">
        <thead><tr><th>Patient</th><th>Admission Date</th><th>Discharge Date</th><th>Doctor</th></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="4">No records found.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($full($r)) ?></td>
                    <td><?= htmlspecialchars($r['admission_date']) ?></td>
                    <td><?= htmlspecialchars($r['discharge_date']) ?></td>
                    <td><?= htmlspecialchars($r['doctor_name']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

<?php endif; ?>