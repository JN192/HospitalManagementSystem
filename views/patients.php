<h1>Patient Registration</h1>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?page=patients">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="patient_id" value="<?= htmlspecialchars($editPatient['patient_id'] ?? '') ?>">

    <label>Patient Code<br><input type="text" name="patient_code" value="<?= htmlspecialchars($editPatient['patient_code'] ?? '') ?>" required></label><br>
    <label>First Name<br><input type="text" name="first_name" value="<?= htmlspecialchars($editPatient['first_name'] ?? '') ?>" required></label><br>
    <label>Last Name<br><input type="text" name="last_name" value="<?= htmlspecialchars($editPatient['last_name'] ?? '') ?>" required></label><br>
    <label>Birth Date<br><input type="date" name="birth_date" value="<?= htmlspecialchars($editPatient['birth_date'] ?? '') ?>" required></label><br>
    <label>Sex<br>
        <select name="sex" required>
            <option value="">-- Select --</option>
            <option value="Male" <?= (($editPatient['sex'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= (($editPatient['sex'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
        </select>
    </label><br>
    <label>Contact Number<br><input type="text" name="contact_number" value="<?= htmlspecialchars($editPatient['contact_number'] ?? '') ?>"></label><br>
    <label>Address<br><input type="text" name="address" value="<?= htmlspecialchars($editPatient['address'] ?? '') ?>"></label><br>
    <label>Emergency Contact<br><input type="text" name="emergency_contact" value="<?= htmlspecialchars($editPatient['emergency_contact'] ?? '') ?>"></label><br><br>

    <button type="submit"><?= $editPatient ? 'UPDATE' : 'SAVE' ?></button>
    <a href="index.php?page=patients"><button type="button">CLEAR</button></a>
</form>

<hr>

<form method="get" action="index.php">
    <input type="hidden" name="page" value="patients">
    <input type="text" name="search" placeholder="Search by name or code..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<table border="1" cellpadding="6" cellspacing="0" style="margin-top:10px; width:100%;">
    <thead>
        <tr><th>Code</th><th>Name</th><th>Birth Date</th><th>Sex</th><th>Contact</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (empty($patients)): ?>
            <tr><td colspan="7">No patients found.</td></tr>
        <?php else: foreach ($patients as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['patient_code']) ?></td>
                <td><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
                <td><?= htmlspecialchars($p['birth_date']) ?></td>
                <td><?= htmlspecialchars($p['sex']) ?></td>
                <td><?= htmlspecialchars($p['contact_number']) ?></td>
                <td><?= htmlspecialchars($p['status']) ?></td>
                <td>
                   <a href="index.php?page=patient_profile&id=<?= $p['patient_id'] ?>">View</a> |
                   <a href="index.php?page=patients&action=edit&id=<?= $p['patient_id'] ?>">Edit</a> |
                    <?php if ($p['status'] === 'Active'): ?>
                        <a href="index.php?page=patients&action=deactivate&id=<?= $p['patient_id'] ?>" onclick="return confirm('Deactivate this patient?');">Deactivate</a>
                    <?php else: ?>
                        <a href="index.php?page=patients&action=activate&id=<?= $p['patient_id'] ?>">Activate</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>