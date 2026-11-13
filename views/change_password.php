<section class="auth-card password-card">
    <h1>Change Password</h1>
    <?php if ($error !== ''): ?>
        <p class="auth-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <p class="auth-message" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="index.php?page=change_password">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <label for="current-password">Current password</label>
        <input id="current-password" name="current_password" type="password" autocomplete="current-password" required>
        <label for="new-password">New password (12–72 characters)</label>
        <input id="new-password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
        <label for="confirm-password">Confirm new password</label>
        <input id="confirm-password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
        <button type="submit">Update Password</button>
    </form>
</section>
