<section class="user-management">
    <h1>User Management</h1>
    <p>Create accounts, assign roles, reset passwords, and deactivate accounts that should no longer sign in.</p>

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

    <section class="user-form-card">
        <h2>Create User</h2>
        <form method="post" action="index.php?page=users" class="user-create-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="create">
            <label for="new-username">Username</label>
            <input id="new-username" name="username" type="text" minlength="3" maxlength="50" autocomplete="off" required>
            <label for="new-password">Temporary password (12–72 characters)</label>
            <input id="new-password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
            <label for="new-role">Role</label>
            <select id="new-role" name="role" required>
                <option value="" selected disabled>Select a role</option>
                <?php foreach (Auth::roles() as $role): ?>
                    <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Create Account</button>
        </form>
    </section>

    <h2>Existing Accounts</h2>
    <div class="user-table-wrap">
        <table class="user-table">
            <thead>
                <tr><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if ($users === []): ?>
                    <tr><td colspan="4">No user accounts found.</td></tr>
                <?php else: foreach ($users as $account): ?>
                    <tr>
                        <td><?= htmlspecialchars($account['username'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($account['role'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($account['status'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="user-actions">
                            <?php if ((int)$account['user_id'] !== $currentUserId): ?>
                                <form method="post" action="index.php?page=users">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="status">
                                    <input type="hidden" name="user_id" value="<?= (int)$account['user_id'] ?>">
                                    <?php if ($account['status'] === 'ACTIVE'): ?>
                                        <input type="hidden" name="status" value="INACTIVE">
                                        <button type="submit" class="user-action-button">Deactivate</button>
                                    <?php else: ?>
                                        <input type="hidden" name="status" value="ACTIVE">
                                        <button type="submit" class="user-action-button">Activate</button>
                                    <?php endif; ?>
                                </form>
                            <?php else: ?>
                                <span>Current account</span>
                            <?php endif; ?>
                            <?php if ((int)$account['user_id'] !== $currentUserId): ?>
                                <form method="post" action="index.php?page=users">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="role">
                                    <input type="hidden" name="user_id" value="<?= (int)$account['user_id'] ?>">
                                    <select name="role" aria-label="Role for <?= htmlspecialchars($account['username'], ENT_QUOTES, 'UTF-8') ?>" required>
                                        <?php foreach (Auth::roles() as $role): ?>
                                            <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" <?= $role === $account['role'] ? 'selected' : '' ?>><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="user-action-button">Update Role</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="index.php?page=users" class="user-reset-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="user_id" value="<?= (int)$account['user_id'] ?>">
                                <input name="password" type="password" minlength="12" maxlength="72" placeholder="New password" aria-label="New password for <?= htmlspecialchars($account['username'], ENT_QUOTES, 'UTF-8') ?>" required>
                                <button type="submit" class="user-action-button">Reset Password</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>
