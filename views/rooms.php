<h1>Room and Bed Management</h1>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?page=rooms">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="room_id" value="<?= htmlspecialchars($editRoom['room_id'] ?? '') ?>">

    <label>Room Number<br><input type="text" name="room_number" value="<?= htmlspecialchars($editRoom['room_number'] ?? '') ?>" required></label><br>
    <label>Room Type<br>
        <select name="room_type" required>
            <option value="">-- Select --</option>
            <?php foreach (['Ward', 'Private', 'Semi-Private', 'ICU', 'Emergency'] as $type): ?>
                <option value="<?= $type ?>" <?= (($editRoom['room_type'] ?? '') === $type) ? 'selected' : '' ?>><?= $type ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Bed Number<br><input type="text" name="bed_number" value="<?= htmlspecialchars($editRoom['bed_number'] ?? '') ?>" required></label><br>
    <label>Room Rate (per day)<br><input type="text" name="room_rate" value="<?= htmlspecialchars($editRoom['room_rate'] ?? '') ?>" required></label><br><br>

    <button type="submit"><?= $editRoom ? 'UPDATE' : 'SAVE' ?></button>
    <a href="index.php?page=rooms"><button type="button">CLEAR</button></a>
</form>

<hr>

<form method="get" action="index.php">
    <input type="hidden" name="page" value="rooms">
    <input type="text" name="search" placeholder="Search by room number, type, or bed..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<table border="1" cellpadding="6" cellspacing="0" style="margin-top:10px; width:100%;">
    <thead>
        <tr><th>Room Number</th><th>Type</th><th>Bed Number</th><th>Rate/Day</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (empty($rooms)): ?>
            <tr><td colspan="6">No rooms found.</td></tr>
        <?php else: foreach ($rooms as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['room_number']) ?></td>
                <td><?= htmlspecialchars($r['room_type']) ?></td>
                <td><?= htmlspecialchars($r['bed_number']) ?></td>
                <td><?= htmlspecialchars(number_format((float)$r['room_rate'], 2)) ?></td>
                <td><?= htmlspecialchars($r['status']) ?></td>
                <td>
                    <a href="index.php?page=rooms&action=edit&id=<?= $r['room_id'] ?>">Edit</a> |
                    <?php if ($r['status'] !== 'Available'): ?>
                        <a href="index.php?page=rooms&action=available&id=<?= $r['room_id'] ?>">Mark Available</a>
                    <?php else: ?>
                        <a href="index.php?page=rooms&action=occupy&id=<?= $r['room_id'] ?>">Mark Occupied</a>
                    <?php endif; ?>
                    | <a href="index.php?page=rooms&action=maintenance&id=<?= $r['room_id'] ?>">Maintenance</a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>