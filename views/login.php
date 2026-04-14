<?php
// Login page — handles its own POST before any HTML output
if (Auth::check()) { redirect('?page=dashboard'); }

$error = '';
if (is_post()) {
    if (!csrf_verify()) { $error = 'Invalid request. Please try again.'; }
    else {
        $email    = trim(post('email'));
        $password = post('password');

        // Basic rate-limit: max 5 failures per 15 minutes (stored in session)
        $attempts = &$_SESSION['_login_attempts'];
        $lock_until = $_SESSION['_login_lock'] ?? 0;

        if ($lock_until > time()) {
            $error = 'Too many failed attempts. Try again in ' . ceil(($lock_until - time()) / 60) . ' minute(s).';
        } elseif (Auth::attempt($email, $password)) {
            unset($_SESSION['_login_attempts'], $_SESSION['_login_lock']);
            redirect('?page=dashboard');
        } else {
            $attempts = ($attempts ?? 0) + 1;
            if ($attempts >= 5) {
                $_SESSION['_login_lock'] = time() + 900;
                unset($_SESSION['_login_attempts']);
                $error = 'Account locked for 15 minutes due to too many failed attempts.';
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo e($cfg['app']['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo e($cfg['app']['url']); ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="theme-auth d-flex align-items-center justify-content-center min-vh-100">
<div class="login-shell w-100 d-flex align-items-center justify-content-center px-3">
<div class="login-box w-100 mx-auto">
    <div class="text-center mb-4">
        <i class="fa fa-hospital fa-3x text-white mb-2"></i>
        <h2 class="text-white fw-bold"><?php echo e($cfg['app']['name']); ?></h2>
        <p class="text-white-50">Clinical Booking System</p>
    </div>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-4">Sign In</h5>

            <?php if ($error): ?>
            <div class="alert alert-danger py-2"><i class="fa fa-exclamation-triangle me-2"></i><?php echo e($error); ?></div>
            <?php endif; ?>

            <?php echo flash_html(); ?>

            <form method="post" autocomplete="on">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        <input type="email" class="form-control" name="email"
                               value="<?php echo e(post('email')); ?>"
                               placeholder="admin@example.com" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" class="form-control" name="password"
                               placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                    <i class="fa fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
        </div>
    </div>

    <p class="text-center text-white-50 mt-3 small">
        <?php echo e($cfg['app']['name']); ?> v<?php echo CLINICALL_VERSION; ?>
    </p>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo e($cfg['app']['url']); ?>/assets/js/app.js"></script>
</body>
</html>
