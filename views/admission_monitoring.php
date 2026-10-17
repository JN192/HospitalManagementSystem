<h1>Currently Admitted Patients</h1>

<form method="get" action="index.php">
    <input type="hidden" name="page" value="admissions_monitor">
    <input type="text" name="search" placeholder="Search admitted patient..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<table border="1" cellpadding="6" cellspacing="0" style="margin-top:10px; width:100%;">
    <thead>
        <tr><th>Patient</th><th>Doctor</th><th>Room</th><th>Admission Date</th><th>Length of Stay</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (empty($admitted)): ?>
            <tr><td colspan="7">No patients currently admitted.</td></tr>
        <?php else: foreach ($admitted as $a):
            $admitDate = new DateTime($a['admission_date']);
            $today = new DateTime();
            $days = $admitDate->diff($today)->days;
        ?>
            <tr>
                <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?> (<?= htmlspecialchars($a['patient_code']) ?>)</td>
                <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                <td><?= htmlspecialchars($a['room_number'] . ' / ' . $a['bed_number']) ?></td>
                <td><?= htmlspecialchars($a['admission_date']) ?></td>
                <td><?= $days ?> day<?= $days !== 1 ? 's' : '' ?></td>
                <td><?= htmlspecialchars($a['status']) ?></td>
                <td><a href="index.php?page=admissions_monitor&view_id=<?= $a['admission_id'] ?>">View Details</a></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<?php if ($viewAdmission): ?>
    <hr>
    <h2>Admission Details</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Patient</th><td><?= htmlspecialchars($viewAdmission['first_name'] . ' ' . $viewAdmission['last_name']) ?></td></tr>
        <tr><th>Attending Doctor</th><td><?= htmlspecialchars($viewAdmission['doctor_name']) ?></td></tr>
        <tr><th>Room / Bed</th><td><?= htmlspecialchars($viewAdmission['room_number'] . ' / ' . $viewAdmission['bed_number']) ?></td></tr>
        <tr><th>Admission Date</th><td><?= htmlspecialchars($viewAdmission['admission_date']) ?></td></tr>
        <tr><th>Admission Type</th><td><?= htmlspecialchars($viewAdmission['admission_type']) ?></td></tr>
        <tr><th>Reason</th><td><?= htmlspecialchars($viewAdmission['reason']) ?></td></tr>
        <tr><th>Status</th><td><?= htmlspecialchars($viewAdmission['status']) ?></td></tr>
    </table>

    <h3>Treatment History for this Admission</h3>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
        <thead><tr><th>Date</th><th>Doctor</th><th>Diagnosis</th><th>Description</th></tr></thead>
        <tbody>
            <?php if (empty($treatments)): ?>
                <tr><td colspan="4">No treatment records for this admission.</td></tr>
            <?php else: foreach ($treatments as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['treatment_date']) ?></td>
                    <td><?= htmlspecialchars($t['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($t['diagnosis']) ?></td>
                    <td><?= htmlspecialchars($t['treatment_description']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
<?php endif; ?>