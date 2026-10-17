<h1>Patient Profile</h1>
<p><a href="index.php?page=patients">&larr; Back to Patients</a></p>

<h2><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>Patient Code</th><td><?= htmlspecialchars($patient['patient_code']) ?></td></tr>
    <tr><th>Birth Date</th><td><?= htmlspecialchars($patient['birth_date']) ?></td></tr>
    <tr><th>Sex</th><td><?= htmlspecialchars($patient['sex']) ?></td></tr>
    <tr><th>Contact Number</th><td><?= htmlspecialchars($patient['contact_number']) ?></td></tr>
    <tr><th>Address</th><td><?= htmlspecialchars($patient['address']) ?></td></tr>
    <tr><th>Emergency Contact</th><td><?= htmlspecialchars($patient['emergency_contact']) ?></td></tr>
    <tr><th>Status</th><td><?= htmlspecialchars($patient['status']) ?></td></tr>
</table>

<h3>Admission History</h3>
<table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
    <thead><tr><th>Date</th><th>Doctor</th><th>Room</th><th>Type</th><th>Reason</th><th>Status</th></tr></thead>
    <tbody>
        <?php if (empty($admissions)): ?>
            <tr><td colspan="6">No admission records.</td></tr>
        <?php else: foreach ($admissions as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['admission_date']) ?></td>
                <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                <td><?= htmlspecialchars($a['room_number'] . ' (' . $a['room_type'] . ')') ?></td>
                <td><?= htmlspecialchars($a['admission_type']) ?></td>
                <td><?= htmlspecialchars($a['reason']) ?></td>
                <td><?= htmlspecialchars($a['status']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<h3>Treatment History</h3>
<table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
    <thead><tr><th>Date</th><th>Doctor</th><th>Diagnosis</th><th>Details</th></tr></thead>
    <tbody>
        <?php if (empty($treatments)): ?>
            <tr><td colspan="4">No treatment records.</td></tr>
        <?php else: foreach ($treatments as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['treatment_date']) ?></td>
                <td><?= htmlspecialchars($t['doctor_name']) ?></td>
                <td><?= htmlspecialchars($t['diagnosis']) ?></td>
                <td>
                    <details>
                        <summary>View details</summary>
                        <p><strong>Symptoms / Clinical Findings:</strong><br><?= nl2br(htmlspecialchars($t['symptoms'] ?? '')) ?: '-' ?></p>
                        <p><strong>Treatment Description:</strong><br><?= nl2br(htmlspecialchars($t['treatment_description'] ?? '')) ?: '-' ?></p>
                        <p><strong>Medications:</strong></p>
                        <?php if (empty($t['medicines'])): ?>
                            <p>No medications ordered.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($t['medicines'] as $m): ?>
                                    <li>
                                        <?= htmlspecialchars($m['medicine_name'] . ' (' . $m['dosage_form'] . ')') ?>
                                        &mdash; <?= htmlspecialchars($m['dosage']) ?>,
                                        <?= htmlspecialchars($m['frequency']) ?>,
                                        for <?= htmlspecialchars($m['duration']) ?>
                                        <?php if (!empty($m['instructions'])): ?>
                                            <br><em><?= htmlspecialchars($m['instructions']) ?></em>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <p><strong>Doctor's Notes:</strong><br><?= nl2br(htmlspecialchars($t['notes'] ?? '')) ?: '-' ?></p>
                        <p><strong>Follow-up Instructions:</strong><br><?= nl2br(htmlspecialchars($t['follow_up_instructions'] ?? '')) ?: '-' ?></p>
                    </details>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<h3>Billing History</h3>
<table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
    <thead><tr><th>Bill Date</th><th>Total Amount</th><th>Status</th></tr></thead>
    <tbody>
        <?php if (empty($bills)): ?>
            <tr><td colspan="3">No billing records.</td></tr>
        <?php else: foreach ($bills as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['bill_date']) ?></td>
                <td><?= htmlspecialchars(number_format((float)$b['total_amount'], 2)) ?></td>
                <td><?= htmlspecialchars($b['status']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<h3>Discharge History</h3>
<table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
    <thead><tr><th>Discharge Date</th><th>Diagnosis</th><th>Instructions</th><th>Follow-up Date</th></tr></thead>
    <tbody>
        <?php if (empty($discharges)): ?>
            <tr><td colspan="4">No discharge records.</td></tr>
        <?php else: foreach ($discharges as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['discharge_date']) ?></td>
                <td><?= htmlspecialchars($d['discharge_diagnosis']) ?></td>
                <td><?= htmlspecialchars($d['discharge_instructions']) ?></td>
                <td><?= htmlspecialchars($d['follow_up_date'] ?? '-') ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>