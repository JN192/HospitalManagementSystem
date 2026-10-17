<h1>Hospital Services and Rates</h1>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?page=services">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="service_id" value="<?= htmlspecialchars($editService['service_id'] ?? '') ?>">

    <label>Service Name<br>
        <input type="text" name="service_name" value="<?= htmlspecialchars($editService['service_name'] ?? '') ?>" required>
    </label><br>

    <label>Category<br>
        <select name="category" required>
            <option value="">-- Select --</option>
            <?php foreach (['Professional', 'Accommodation', 'Diagnostic', 'Hospital', 'Pharmacy'] as $cat): ?>
                <option value="<?= $cat ?>" <?= (($editService['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Rate<br>
        <input type="text" name="rate" value="<?= htmlspecialchars($editService['rate'] ?? '') ?>" placeholder="e.g. 500" required>
    </label><br><br>

    <button type="submit"><?= !empty($editService['service_id']) ? 'UPDATE' : 'SAVE' ?></button>
    <a href="index.php?page=services"><button type="button">CLEAR</button></a>
</form>

<hr>

<form method="get" action="index.php">
    <input type="hidden" name="page" value="services">
    <input type="text" name="search" placeholder="Search by service or category..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<table border="1" cellpadding="6" cellspacing="0" style="margin-top:10px; width:100%;">
    <thead>
        <tr><th>Service</th><th>Category</th><th>Rate</th><th>Set Rate</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (empty($services)): ?>
            <tr><td colspan="6">No services found.</td></tr>
        <?php else: foreach ($services as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['service_name']) ?></td>
                <td><?= htmlspecialchars($s['category']) ?></td>
                <td><?= htmlspecialchars(number_format((float)$s['rate'], 2)) ?></td>
                <td>
                    <form method="post" action="index.php?page=services" style="margin:0;">
                        <input type="hidden" name="action" value="set_rate">
                        <input type="hidden" name="service_id" value="<?= $s['service_id'] ?>">
                        <input type="text" name="rate" size="8" placeholder="New rate" required>
                        <button type="submit">Set</button>
                    </form>
                </td>
                <td><?= htmlspecialchars($s['status']) ?></td>
                <td>
                    <a href="index.php?page=services&action=edit&id=<?= $s['service_id'] ?>">Edit</a> |
                    <?php if ($s['status'] === 'Active'): ?>
                        <a href="index.php?page=services&action=deactivate&id=<?= $s['service_id'] ?>" onclick="return confirm('Deactivate this service?');">Deactivate</a>
                    <?php else: ?>
                        <a href="index.php?page=services&action=activate&id=<?= $s['service_id'] ?>">Activate</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>