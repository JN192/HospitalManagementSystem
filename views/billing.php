<?php $peso = fn($n) => '₱' . number_format((float)$n, 2); ?>

<h1>Patient Billing</h1>

<?php if (isset($_GET['generated'])): ?>
    <p style="color:green;">Bill generated successfully.</p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (!$bill): ?>

    <p>Generate a bill for an admission, or open an existing one.</p>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
        <thead>
            <tr><th>Admission</th><th>Patient</th><th>Admitted</th><th>Room</th><th>Admission Status</th><th>Bill Status</th><th>Total</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if (empty($admissions)): ?>
                <tr><td colspan="8">No admissions found.</td></tr>
            <?php else: foreach ($admissions as $a): ?>
                <tr>
                    <td>ADM-<?= str_pad($a['admission_id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?> (<?= htmlspecialchars($a['patient_code']) ?>)</td>
                    <td><?= htmlspecialchars($a['admission_date']) ?></td>
                    <td><?= htmlspecialchars($a['room_number']) ?></td>
                    <td><?= htmlspecialchars($a['admission_status']) ?></td>
                    <td><?= $a['bill_id'] ? htmlspecialchars($a['bill_status']) : 'No bill yet' ?></td>
                    <td><?= $a['bill_id'] ? $peso($a['total_amount']) : '-' ?></td>
                    <td>
                        <?php if ($a['bill_id']): ?>
                            <a href="index.php?page=billing&bill_id=<?= $a['bill_id'] ?>">View Bill</a>
                        <?php else: ?>
                            <form method="post" action="index.php?page=billing" style="margin:0;">
                                <input type="hidden" name="action" value="generate">
                                <input type="hidden" name="admission_id" value="<?= $a['admission_id'] ?>">
                                <button type="submit">Generate Bill</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

<?php else: ?>

    <p><a href="index.php?page=billing">&larr; Back to Billing</a></p>

    <h2>PATIENT BILL</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Patient</th><td><?= htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']) ?> (<?= htmlspecialchars($bill['patient_code']) ?>)</td></tr>
        <tr><th>Admission</th><td>ADM-<?= str_pad($bill['admission_id'], 5, '0', STR_PAD_LEFT) ?></td></tr>
        <tr><th>Attending Doctor</th><td><?= htmlspecialchars($bill['doctor_name']) ?></td></tr>
        <tr><th>Room / Bed</th><td><?= htmlspecialchars($bill['room_number'] . ' / ' . $bill['bed_number']) ?></td></tr>
        <tr><th>Admission Date</th><td><?= htmlspecialchars($bill['admission_date']) ?></td></tr>
        <tr><th>Bill Date</th><td><?= htmlspecialchars($bill['bill_date']) ?></td></tr>
        <tr><th>Bill Status</th><td><?= htmlspecialchars($bill['status']) ?></td></tr>
    </table>

    <h3>Charges</h3>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
        <thead><tr><th>Description</th><th>Amount</th><th>Action</th></tr></thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="3">No charges yet.</td></tr>
            <?php else: foreach ($items as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['description']) ?></td>
                    <td style="text-align:right;"><?= $peso($it['amount']) ?></td>
                    <td>
                        <?php if ($bill['status'] !== 'Paid'): ?>
                            <form method="post" action="index.php?page=billing" style="margin:0;" onsubmit="return confirm('Remove this charge?');">
                                <input type="hidden" name="action" value="remove_item">
                                <input type="hidden" name="bill_id" value="<?= $bill['bill_id'] ?>">
                                <input type="hidden" name="item_id" value="<?= $it['bill_item_id'] ?>">
                                <button type="submit">Remove</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            <tr>
                <th>TOTAL</th>
                <th style="text-align:right;"><?= $peso($bill['total_amount']) ?></th>
                <th></th>
            </tr>
        </tbody>
    </table>

    <?php if ($bill['status'] !== 'Paid'): ?>
        <hr>
        <h3>Add Charges</h3>

        <form method="post" action="index.php?page=billing">
            <input type="hidden" name="action" value="add_service">
            <input type="hidden" name="bill_id" value="<?= $bill['bill_id'] ?>">
            <strong>Hospital Service:</strong>
            <select name="service_id" required>
                <option value="">-- Select Service --</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?= $s['service_id'] ?>"><?= htmlspecialchars($s['service_name'] . ' - ' . $peso($s['rate'])) ?></option>
                <?php endforeach; ?>
            </select>
            Qty: <input type="number" name="quantity" value="1" min="1" style="width:60px;">
            <button type="submit">Add Service</button>
        </form>
        <br>

        <form method="post" action="index.php?page=billing">
            <input type="hidden" name="action" value="add_medicine">
            <input type="hidden" name="bill_id" value="<?= $bill['bill_id'] ?>">
            <strong>Medicine:</strong>
            <select name="medicine_id" required>
                <option value="">-- Select Medicine --</option>
                <?php foreach ($medicines as $m): ?>
                    <option value="<?= $m['medicine_id'] ?>"><?= htmlspecialchars($m['medicine_name'] . ' (' . $m['dosage_form'] . ') - ' . $peso($m['unit_price'])) ?></option>
                <?php endforeach; ?>
            </select>
            Qty: <input type="number" name="quantity" value="1" min="1" style="width:60px;">
            <button type="submit">Add Medicine</button>
        </form>
        <br>

        <form method="post" action="index.php?page=billing">
            <input type="hidden" name="action" value="add_other">
            <input type="hidden" name="bill_id" value="<?= $bill['bill_id'] ?>">
            <strong>Other Charge:</strong>
            <input type="text" name="description" placeholder="Description" required>
            <input type="text" name="amount" placeholder="Amount" size="10" required>
            <button type="submit">Add Charge</button>
        </form>
        <br>

        <form method="post" action="index.php?page=billing">
            <input type="hidden" name="action" value="refresh_room">
            <input type="hidden" name="bill_id" value="<?= $bill['bill_id'] ?>">
            <button type="submit">Recalculate Room Charges</button>
            <small>(updates the room line to the current length of stay)</small>
        </form>
    <?php endif; ?>

    <hr>
    <?php if ($bill['status'] === 'Paid'): ?>
        <p><strong>This bill is fully paid.</strong>
           <a href="index.php?page=payments&bill_id=<?= $bill['bill_id'] ?>">View payments</a></p>
    <?php else: ?>
        <a href="index.php?page=payments&bill_id=<?= $bill['bill_id'] ?>"><button type="button">PROCEED TO PAYMENT</button></a>
    <?php endif; ?>

<?php endif; ?>