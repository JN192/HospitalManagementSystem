<?php $peso = fn($amount) => '₱' . number_format((float)$amount, 2); ?>

<section class="discharge-page">
    <h1>Discharge Patients</h1>
    <p>Record the discharge details to close an active admission and release its room or bed.</p>

    <?php if ($success !== ''): ?>
        <p class="auth-message" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($errors !== []): ?>
        <ul class="auth-error" role="alert">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($activeAdmissions === []): ?>
        <p>No active admissions are available to discharge.</p>
    <?php else: ?>
        <div class="discharge-table-wrap">
            <table class="discharge-table">
                <thead>
                    <tr><th>Patient</th><th>Doctor</th><th>Room / Bed</th><th>Admitted</th><th>Bill Balance</th><th>Discharge Details</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($activeAdmissions as $admission):
                        $balance = max(0, (float)$admission['total_amount'] - (float)$admission['total_paid']);
                    ?>
                        <tr>
                            <td>
                                <a href="index.php?page=patient_profile&amp;id=<?= (int)$admission['patient_id'] ?>">
                                    <?= htmlspecialchars($admission['first_name'] . ' ' . $admission['last_name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                <small><?= htmlspecialchars($admission['patient_code'], ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><?= htmlspecialchars($admission['doctor_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($admission['room_number'] . ' / ' . $admission['bed_number'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($admission['admission_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($admission['bill_id']): ?>
                                    <?= $peso($balance) ?>
                                    <?php if ($balance > 0 && Auth::canAccessPage($_SESSION['auth_user']['role'], 'payments')): ?>
                                        <a href="index.php?page=payments&amp;bill_id=<?= (int)$admission['bill_id'] ?>">Record payment</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    No bill yet
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" action="index.php?page=discharge" class="discharge-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="discharge">
                                    <input type="hidden" name="admission_id" value="<?= (int)$admission['admission_id'] ?>">
                                    <label>Discharge date
                                        <input type="date" name="discharge_date" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" min="<?= htmlspecialchars($admission['admission_date'], ENT_QUOTES, 'UTF-8') ?>" max="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>" required>
                                    </label>
                                    <label>Diagnosis
                                        <textarea name="discharge_diagnosis" rows="2" maxlength="2000" required></textarea>
                                    </label>
                                    <label>Instructions
                                        <textarea name="discharge_instructions" rows="2" maxlength="4000" required></textarea>
                                    </label>
                                    <label>Follow-up date (optional)
                                        <input type="date" name="follow_up_date" min="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>">
                                    </label>
                                    <button type="submit" onclick="return confirm('Record discharge and release this room/bed?');">Discharge &amp; Release Bed</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
