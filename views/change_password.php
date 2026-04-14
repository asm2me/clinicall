<?php
Auth::requireLogin();

$page_title = 'Change Password';
$user = Auth::user();
$errors = [];

if (is_post() && csrf_verify()) {
    $current_password = (string) post('current_password');
    $new_password = (string) post('new_password');
    $confirm_password = (string) post('confirm_password');

    $dbUser = Database::row(
        "SELECT id, password FROM users WHERE id = :id LIMIT 1",
        [':id' => Auth::userId()]
    );

    if (!$dbUser || !password_verify($current_password, $dbUser['password'])) {
        $errors[] = 'Current password is incorrect.';
    }

    if (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'New password confirmation does not match.';
    }

    if ($current_password === $new_password) {
        $errors[] = 'New password must be different from the current password.';
    }

    if (empty($errors)) {
        Database::update('users', [
            'password' => Auth::hashPassword($new_password),
            'updated_at' => date('Y-m-d H:i:s'),
        ], [
            'id' => Auth::userId(),
        ]);

        flash('Password changed successfully.');
        redirect('?page=dashboard');
    }
}

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-5 col-lg-6">
        <div class="card border-0 shadow-sm modern-panel">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1"><i class="fa fa-key me-2 text-primary"></i>Change Password</h4>
                    <div class="text-muted small">Update your account password securely.</div>
                </div>
                <span class="badge bg-white text-primary"><?php echo e($user['email'] ?? ''); ?></span>
            </div>
            <div class="card-body">
                <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="post">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password" required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-shield-halved me-1"></i>Update Password
                        </button>
                        <a href="?page=dashboard" class="btn btn-outline-secondary">Cancel</a>
                        <a href="?page=logout" class="btn btn-outline-danger ms-auto">
                            <i class="fa fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once CLINICALL_ROOT . '/views/layout/footer.php'; ?>
