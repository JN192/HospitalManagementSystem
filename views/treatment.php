<h1>Treatment Record</h1>

<?php if (isset($_GET['success'])): ?>
    <p style="color:green;">Treatment recorded successfully.</p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?page=treatment">
    <input type="hidden" name="action" value="save">

    <label>Patient / Admission<br>
        <select name="admission_id" required>
            <option value="">-- Select Active Admission --</option>
            <?php foreach ($admissions as $a): ?>
                <option value="<?= $a['admission_id'] ?>">
                    <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name'] . ' - Admission #' . $a['admission_id'] . ' (' . $a['admission_date'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($admissions)): ?><span style="color:red;"> No active admissions available.</span><?php endif; ?>
    </label><br>

    <label>Doctor<br>
        <select name="doctor_id" required>
            <option value="">-- Select Doctor --</option>
            <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['doctor_id'] ?>"><?= htmlspecialchars($d['doctor_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Treatment Date<br><input type="date" name="treatment_date" required></label><br>
    <label>Diagnosis<br><textarea name="diagnosis" rows="2" cols="50" required></textarea></label><br>
    <label>Symptoms / Clinical Findings<br><textarea name="symptoms" rows="2" cols="50"></textarea></label><br>
    <label>Treatment Description<br><textarea name="treatment_description" rows="3" cols="50" required></textarea></label><br>
    <label>Doctor's Notes<br><textarea name="notes" rows="2" cols="50"></textarea></label><br>
    <label>Follow-up Instructions<br><textarea name="follow_up_instructions" rows="2" cols="50"></textarea></label><br><br>

    <button type="submit">SAVE TREATMENT</button>
</form>

<hr>
<h3>All Treatment Records (for verification)</h3>
<table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
    <thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Description</th></tr></thead>
    <tbody>
        <?php if (empty($treatments)): ?>
            <tr><td colspan="5">No treatments recorded yet.</td></tr>
        <?php else: foreach ($treatments as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['treatment_date']) ?></td>
                <td><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></td>
                <td><?= htmlspecialchars($t['doctor_name']) ?></td>
                <td><?= htmlspecialchars($t['diagnosis']) ?></td>
                <td><?= htmlspecialchars($t['treatment_description']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>