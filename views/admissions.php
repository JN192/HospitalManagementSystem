<h1>Patient Admission</h1>

<?php if (isset($_GET['success'])): ?>
    <p style="color:green;">Patient admitted successfully.</p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?page=admissions">
    <input type="hidden" name="action" value="admit">

    <label>Patient<br>
        <select name="patient_id" required>
            <option value="">-- Select Patient --</option>
            <?php foreach ($patients as $p): ?>
                <option value="<?= $p['patient_id'] ?>">
                    <?= htmlspecialchars($p['patient_code'] . ' - ' . $p['first_name'] . ' ' . $p['last_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($patients)): ?><span style="color:red;"> No active patients available.</span><?php endif; ?>
    </label><br>

    <label>Doctor<br>
        <select name="doctor_id" required>
            <option value="">-- Select Doctor --</option>
            <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['doctor_id'] ?>">
                    <?= htmlspecialchars($d['doctor_name'] . ' (' . $d['department_name'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($doctors)): ?><span style="color:red;"> No active doctors available.</span><?php endif; ?>
    </label><br>

    <label>Room / Bed<br>
        <select name="room_id" required>
            <option value="">-- Select Room --</option>
            <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['room_id'] ?>">
                    <?= htmlspecialchars($r['room_number'] . ' - Bed ' . $r['bed_number'] . ' (' . $r['room_type'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($rooms)): ?><span style="color:red;"> No available rooms.</span><?php endif; ?>
    </label><br>

    <label>Admission Type<br>
        <select name="admission_type" required>
            <option value="">-- Select --</option>
            <option value="Emergency">Emergency</option>
            <option value="Regular">Regular</option>
        </select>
    </label><br>

    <label>Admission Date<br><input type="date" name="admission_date" required></label><br>
    <label>Admission Time<br><input type="time" name="admission_time" required></label><br>
    <label>Reason<br><textarea name="reason" rows="3" cols="50" required></textarea></label><br><br>

    <button type="submit">ADMIT PATIENT</button>
</form>

<hr>
<h3>All Admissions (for verification)</h3>
<table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
    <thead><tr><th>Patient</th><th>Doctor</th><th>Room</th><th>Date</th><th>Type</th><th>Status</th></tr></thead>
    <tbody>
        <?php if (empty($admissions)): ?>
            <tr><td colspan="6">No admissions yet.</td></tr>
        <?php else: foreach ($admissions as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
                <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                <td><?= htmlspecialchars($a['room_number'] . ' / ' . $a['bed_number']) ?></td>
                <td><?= htmlspecialchars($a['admission_date']) ?></td>
                <td><?= htmlspecialchars($a['admission_type']) ?></td>
                <td><?= htmlspecialchars($a['status']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>