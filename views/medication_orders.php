<h1>Medication and Treatment Orders</h1>

<?php if (isset($_GET['success'])): ?>
    <p style="color:green;">Medication order added successfully.</p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?page=medication_orders">
    <input type="hidden" name="action" value="order">

    <label>Treatment Record<br>
        <select name="treatment_id" required>
            <option value="">-- Select Treatment --</option>
            <?php foreach ($treatments as $t): ?>
                <option value="<?= $t['treatment_id'] ?>">
                    #<?= $t['treatment_id'] ?> - <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?> (<?= htmlspecialchars($t['treatment_date']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($treatments)): ?><span style="color:red;"> No treatment records yet.</span><?php endif; ?>
    </label><br>

    <label>Medicine<br>
        <select name="medicine_id" required>
            <option value="">-- Select Medicine --</option>
            <?php foreach ($medicines as $m): ?>
                <option value="<?= $m['medicine_id'] ?>"><?= htmlspecialchars($m['medicine_name'] . ' (' . $m['dosage_form'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($medicines)): ?><span style="color:red;"> No medicines in the system yet. <a href="index.php?page=medicines">Add one here.</a></span><?php endif; ?>
    </label><br>

    <label>Dosage<br><input type="text" name="dosage" placeholder="e.g. 500mg" required></label><br>
    <label>Frequency<br><input type="text" name="frequency" placeholder="e.g. 3x a day" required></label><br>
    <label>Duration<br><input type="text" name="duration" placeholder="e.g. 7 days" required></label><br>
    <label>Instructions<br><textarea name="instructions" rows="2" cols="50"></textarea></label><br><br>

    <button type="submit">ADD MEDICATION ORDER</button>
</form>

<hr>
<h3>View Medication Orders for a Treatment</h3>
<form method="get" action="index.php">
    <input type="hidden" name="page" value="medication_orders">
    <select name="view_treatment" onchange="this.form.submit()">
        <option value="">-- Select Treatment to View Orders --</option>
        <?php foreach ($treatments as $t): ?>
            <option value="<?= $t['treatment_id'] ?>" <?= $viewTreatmentId == $t['treatment_id'] ? 'selected' : '' ?>>
                #<?= $t['treatment_id'] ?> - <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?> (<?= htmlspecialchars($t['treatment_date']) ?>)
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($viewTreatmentId): ?>
    <table border="1" cellpadding="6" cellspacing="0" style="margin-top:10px; width:100%;">
        <thead><tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Instructions</th></tr></thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="5">No medication orders for this treatment yet.</td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr>
                    <td><?= htmlspecialchars($o['medicine_name']) ?></td>
                    <td><?= htmlspecialchars($o['dosage']) ?></td>
                    <td><?= htmlspecialchars($o['frequency']) ?></td>
                    <td><?= htmlspecialchars($o['duration']) ?></td>
                    <td><?= htmlspecialchars($o['instructions']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
<?php endif; ?>