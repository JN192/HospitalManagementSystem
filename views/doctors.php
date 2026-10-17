<h1>Doctor Management</h1>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?page=doctors">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($editDoctor['doctor_id'] ?? '') ?>">

    <label>Doctor Code<br><input type="text" name="doctor_code" value="<?= htmlspecialchars($editDoctor['doctor_code'] ?? '') ?>" required></label><br>
    <label>Doctor Name<br><input type="text" name="doctor_name" value="<?= htmlspecialchars($editDoctor['doctor_name'] ?? '') ?>" required></label><br>
    <label>Specialization<br><input type="text" name="specialization" value="<?= htmlspecialchars($editDoctor['specialization'] ?? '') ?>" required></label><br>
    <label>Department<br>
        <select name="department_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= $d['department_id'] ?>" <?= (($editDoctor['department_id'] ?? '') == $d['department_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['department_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Contact Number<br><input type="text" name="contact_number" value="<?= htmlspecialchars($editDoctor['contact_number'] ?? '') ?>"></label><br><br>

    <button type="submit"><?= $editDoctor ? 'UPDATE' : 'SAVE' ?></button>
    <a href="index.php?page=doctors"><button type="button">CLEAR</button></a>
</form>

<hr>

<form method="get" action="index.php">
    <input type="hidden" name="page" value="doctors">
    <input type="text" name="search" placeholder="Search by name, code, or specialization..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<table border="1" cellpadding="6" cellspacing="0" style="margin-top:10px; width:100%;">
    <thead>
        <tr><th>Code</th><th>Name</th><th>Specialization</th><th>Department</th><th>Contact</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (empty($doctors)): ?>
            <tr><td colspan="7">No doctors found.</td></tr>
        <?php else: foreach ($doctors as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['doctor_code']) ?></td>
                <td><?= htmlspecialchars($d['doctor_name']) ?></td>
                <td><?= htmlspecialchars($d['specialization']) ?></td>
                <td><?= htmlspecialchars($d['department_name']) ?></td>
                <td><?= htmlspecialchars($d['contact_number']) ?></td>
                <td><?= htmlspecialchars($d['status']) ?></td>
                <td>
                    <a href="index.php?page=doctors&action=edit&id=<?= $d['doctor_id'] ?>">Edit</a> |
                    <?php if ($d['status'] === 'Active'): ?>
                        <a href="index.php?page=doctors&action=deactivate&id=<?= $d['doctor_id'] ?>" onclick="return confirm('Deactivate this doctor?');">Deactivate</a>
                    <?php else: ?>
                        <a href="index.php?page=doctors&action=activate&id=<?= $d['doctor_id'] ?>">Activate</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>