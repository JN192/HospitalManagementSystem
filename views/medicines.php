<h1>Medicine Management</h1>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?page=medicines">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="medicine_id" value="<?= htmlspecialchars($editMedicine['medicine_id'] ?? '') ?>">

    <label>Medicine Name<br><input type="text" name="medicine_name" value="<?= htmlspecialchars($editMedicine['medicine_name'] ?? '') ?>" required></label><br>
    <label>Dosage Form<br>
        <select name="dosage_form" required>
            <option value="">-- Select --</option>
            <?php foreach (['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Drops'] as $form): ?>
                <option value="<?= $form ?>" <?= (($editMedicine['dosage_form'] ?? '') === $form) ? 'selected' : '' ?>><?= $form ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Unit Price<br><input type="text" name="unit_price" value="<?= htmlspecialchars($editMedicine['unit_price'] ?? '') ?>" required></label><br><br>

    <button type="submit"><?= $editMedicine ? 'UPDATE' : 'SAVE' ?></button>
    <a href="index.php?page=medicines"><button type="button">CLEAR</button></a>
</form>

<hr>

<form method="get" action="index.php">
    <input type="hidden" name="page" value="medicines">
    <input type="text" name="search" placeholder="Search by name or dosage form..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<table border="1" cellpadding="6" cellspacing="0" style="margin-top:10px; width:100%;">
    <thead>
        <tr><th>Name</th><th>Dosage Form</th><th>Unit Price</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (empty($medicines)): ?>
            <tr><td colspan="4">No medicines found.</td></tr>
        <?php else: foreach ($medicines as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['medicine_name']) ?></td>
                <td><?= htmlspecialchars($m['dosage_form']) ?></td>
                <td><?= htmlspecialchars(number_format((float)$m['unit_price'], 2)) ?></td>
                <td>
                    <a href="index.php?page=medicines&action=edit&id=<?= $m['medicine_id'] ?>">Edit</a> |
                    <a href="index.php?page=medicines&action=delete&id=<?= $m['medicine_id'] ?>" onclick="return confirm('Delete this medicine?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>