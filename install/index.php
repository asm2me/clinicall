<?php
/**
 * ClinicAll — Install Wizard
 * Run once, then set 'installed' => true in config.php.
 */
define('CLINICALL_ROOT', dirname(__DIR__));
define('CLINICALL_VERSION', '2.0');

// Load helpers only (no DB yet)
require_once CLINICALL_ROOT . '/core/helpers.php';
require_once CLINICALL_ROOT . '/core/Database.php';

$config_file = CLINICALL_ROOT . '/config.php';
$cfg         = require $config_file;

// Already installed?
if ($cfg['app']['installed']) {
    header('Location: ../index.php');
    exit;
}

session_name('clinicall_install');
session_start();

$step   = (int)($_GET['step'] ?? 1);
$errors = [];
$info   = [];

// ── STEP 1: Requirements check ────────────────────────────────────────────
if ($step === 1) {
    $checks = [
        'PHP >= 8.0'              => version_compare(PHP_VERSION, '8.0.0', '>='),
        'PDO extension'           => extension_loaded('pdo'),
        'PDO PostgreSQL or MySQL' => extension_loaded('pdo_pgsql') || extension_loaded('pdo_mysql'),
        'json extension'          => extension_loaded('json'),
        'session extension'       => extension_loaded('session'),
        'config.php writable'     => is_writable($config_file),
    ];
    $all_ok = !in_array(false, $checks, true);
}

// ── STEP 2: DB config ─────────────────────────────────────────────────────
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = [
        'driver'   => in_array($_POST['driver'],['pgsql','mysql']) ? $_POST['driver'] : 'pgsql',
        'host'     => trim($_POST['host']     ?? '127.0.0.1'),
        'port'     => trim($_POST['port']     ?? '5432'),
        'name'     => trim($_POST['name']     ?? ''),
        'user'     => trim($_POST['user']     ?? ''),
        'password' => trim($_POST['password'] ?? ''),
        'charset'  => 'utf8',
    ];
    if (!$db['name']) { $errors[] = 'Database name is required.'; }
    if (!$db['user']) { $errors[] = 'Database user is required.'; }

    if (empty($errors)) {
        try {
            Database::connect($db);
            Database::get()->query('SELECT 1');
            $_SESSION['install_db'] = $db;
            header('Location: index.php?step=3');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Connection failed: ' . $e->getMessage();
        }
    }
}

// ── STEP 3: Run schema ────────────────────────────────────────────────────
if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = $_SESSION['install_db'] ?? null;
    if (!$db) { header('Location: index.php?step=2'); exit; }

    try {
        Database::connect($db);
        $sql_file = __DIR__ . '/' . ($db['driver'] === 'mysql' ? 'mysql.sql' : 'pgsql.sql');
        $sql      = file_get_contents($sql_file);

        // Split on semicolons (crude but works for our DDL)
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if ($stmt) {
                try { Database::get()->exec($stmt . ';'); }
                catch (Throwable $e) {
                    // Ignore "already exists" errors
                    if (!str_contains($e->getMessage(), 'already exists') &&
                        !str_contains($e->getMessage(), 'Duplicate key')) {
                        throw $e;
                    }
                }
            }
        }
        $_SESSION['install_schema_done'] = true;
        header('Location: index.php?step=4');
        exit;
    } catch (Throwable $e) {
        $errors[] = 'Schema error: ' . $e->getMessage();
    }
}

// ── STEP 4: Create superadmin & first tenant ──────────────────────────────
if ($step === 4 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = $_SESSION['install_db'] ?? null;
    if (!$db || empty($_SESSION['install_schema_done'])) { header('Location: index.php?step=2'); exit; }

    $org_name   = trim($_POST['org_name']     ?? '');
    $org_slug   = strtolower(preg_replace('/[^a-z0-9\-]/','',str_replace(' ','-',$org_name)));
    $admin_name = trim($_POST['admin_name']   ?? '');
    $admin_email= strtolower(trim($_POST['admin_email']  ?? ''));
    $admin_pass = $_POST['admin_password']    ?? '';
    $admin_pass2= $_POST['admin_password2']   ?? '';
    $app_name   = trim($_POST['app_name']     ?? 'ClinicAll');
    $app_url    = rtrim(trim($_POST['app_url'] ?? ''), '/');

    if (!$org_name)    { $errors[] = 'Organization name is required.'; }
    if (!$admin_name)  { $errors[] = 'Admin name is required.'; }
    if (!$admin_email || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid admin email is required.'; }
    if (strlen($admin_pass) < 8) { $errors[] = 'Password must be at least 8 characters.'; }
    if ($admin_pass !== $admin_pass2) { $errors[] = 'Passwords do not match.'; }

    if (empty($errors)) {
        try {
            Database::connect($db);

            $tenant_id = generate_uuid();
            Database::insert('tenants', [
                'id'        => $tenant_id,
                'name'      => $org_name,
                'slug'      => $org_slug ?: 'default',
                'plan'      => 'standard',
                'enabled'   => true,
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=> date('Y-m-d H:i:s'),
            ]);

            Database::insert('users', [
                'id'         => generate_uuid(),
                'tenant_id'  => $tenant_id,
                'name'       => $admin_name,
                'email'      => $admin_email,
                'password'   => password_hash($admin_pass, PASSWORD_BCRYPT, ['cost'=>12]),
                'role'       => 'superadmin',
                'enabled'    => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Write config.php
            $new_cfg = <<<PHP
<?php
return [
    'db' => [
        'driver'   => '{$db['driver']}',
        'host'     => '{$db['host']}',
        'port'     => '{$db['port']}',
        'name'     => '{$db['name']}',
        'user'     => '{$db['user']}',
        'password' => '{$db['password']}',
        'charset'  => 'utf8',
    ],
    'app' => [
        'name'      => '$app_name',
        'url'       => '$app_url',
        'debug'     => false,
        'timezone'  => 'UTC',
        'installed' => true,
    ],
    'session' => [
        'name'     => 'clinicall_sess',
        'lifetime' => 7200,
        'secure'   => false,
    ],
];
PHP;
            file_put_contents($config_file, $new_cfg);

            header('Location: index.php?step=5');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Setup failed: ' . $e->getMessage();
        }
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClinicAll Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; }
        .install-wrap { max-width: 680px; margin: 40px auto; }
        .step-badge { width:2rem;height:2rem;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-weight:700; }
    </style>
</head>
<body>
<div class="install-wrap px-3">
    <div class="text-center mb-4">
        <i class="fa fa-hospital fa-3x text-primary mb-2"></i>
        <h2 class="fw-bold">ClinicAll Setup Wizard</h2>
        <p class="text-muted">Multi-tenant Clinical Booking System v<?php echo CLINICALL_VERSION; ?></p>
    </div>

    <!-- Steps indicator -->
    <div class="d-flex justify-content-center gap-3 mb-4 flex-wrap">
        <?php foreach (['Requirements','Database','Schema','Account','Done'] as $i=>$s): ?>
        <div class="text-center">
            <div class="step-badge <?php echo $step===$i+1?'bg-primary text-white':($step>$i+1?'bg-success text-white':'bg-light border text-muted'); ?>">
                <?php echo $step>$i+1?'✓':($i+1); ?>
            </div>
            <div class="small mt-1 <?php echo $step===$i+1?'fw-bold text-primary':'text-muted'; ?>"><?php echo $s; ?></div>
        </div>
        <?php if ($i<4): ?><div class="mt-2 text-muted">—</div><?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="card border-0 shadow">
        <div class="card-body p-4">

        <?php if ($step === 1): ?>
        <h5>Step 1: Requirements Check</h5>
        <table class="table">
            <?php foreach ($checks as $label => $ok): ?>
            <tr>
                <td><?php echo htmlspecialchars($label); ?></td>
                <td><?php echo $ok?'<span class="text-success fw-bold">✓ OK</span>':'<span class="text-danger fw-bold">✗ Missing</span>'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php if ($all_ok): ?>
        <a href="?step=2" class="btn btn-primary">Continue <i class="fa fa-arrow-right ms-1"></i></a>
        <?php else: ?>
        <div class="alert alert-warning">Please resolve the issues above, then <a href="?step=1">re-check</a>.</div>
        <?php endif; ?>

        <?php elseif ($step === 2): ?>
        <h5>Step 2: Database Connection</h5>
        <form method="post">
            <div class="mb-3">
                <label class="form-label fw-semibold">Driver</label>
                <select class="form-select" name="driver" onchange="setPort(this.value)">
                    <option value="pgsql">PostgreSQL</option>
                    <option value="mysql">MySQL / MariaDB</option>
                </select>
            </div>
            <div class="row">
                <div class="col-8 mb-3"><label class="form-label fw-semibold">Host</label><input type="text" class="form-control" name="host" value="127.0.0.1"></div>
                <div class="col-4 mb-3"><label class="form-label fw-semibold">Port</label><input type="text" class="form-control" name="port" id="port_field" value="5432"></div>
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">Database Name</label><input type="text" class="form-control" name="name" required placeholder="clinicall"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Username</label><input type="text" class="form-control" name="user" required placeholder="clinicall"></div>
            <div class="mb-4"><label class="form-label fw-semibold">Password</label><input type="password" class="form-control" name="password"></div>
            <button type="submit" class="btn btn-primary">Test & Continue <i class="fa fa-arrow-right ms-1"></i></button>
        </form>
        <script>function setPort(d){document.getElementById('port_field').value=d==='mysql'?'3306':'5432';}</script>

        <?php elseif ($step === 3): ?>
        <h5>Step 3: Create Database Tables</h5>
        <p class="text-muted">This will run the SQL schema to create all required tables.</p>
        <?php $db = $_SESSION['install_db'] ?? []; ?>
        <dl class="row small">
            <dt class="col-4">Driver</dt><dd class="col-8"><?php echo htmlspecialchars($db['driver']??''); ?></dd>
            <dt class="col-4">Host</dt><dd class="col-8"><?php echo htmlspecialchars($db['host']??''); ?></dd>
            <dt class="col-4">Database</dt><dd class="col-8"><?php echo htmlspecialchars($db['name']??''); ?></dd>
        </dl>
        <form method="post">
            <button type="submit" class="btn btn-primary"><i class="fa fa-database me-2"></i>Run Schema</button>
        </form>

        <?php elseif ($step === 4): ?>
        <h5>Step 4: Create Account &amp; First Tenant</h5>
        <form method="post">
            <h6 class="text-muted mb-3 border-bottom pb-2">Organization</h6>
            <div class="mb-3">
                <label class="form-label fw-semibold">Organization / Clinic Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="org_name" required placeholder="City Medical Center"
                       value="<?php echo htmlspecialchars($_POST['org_name']??''); ?>">
            </div>

            <h6 class="text-muted mb-3 border-bottom pb-2 mt-4">Superadmin Account</h6>
            <div class="mb-3"><label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="admin_name" required value="<?php echo htmlspecialchars($_POST['admin_name']??''); ?>"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label><input type="email" class="form-control" name="admin_email" required value="<?php echo htmlspecialchars($_POST['admin_email']??''); ?>"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Password <span class="text-danger">*</span></label><input type="password" class="form-control" name="admin_password" required minlength="8"></div>
            <div class="mb-4"><label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label><input type="password" class="form-control" name="admin_password2" required minlength="8"></div>

            <h6 class="text-muted mb-3 border-bottom pb-2 mt-4">Application Settings</h6>
            <div class="mb-3"><label class="form-label fw-semibold">App Name</label><input type="text" class="form-control" name="app_name" value="ClinicAll"></div>
            <div class="mb-4">
                <label class="form-label fw-semibold">App URL</label>
                <input type="text" class="form-control" name="app_url"
                       placeholder="http://localhost/clinicall"
                       value="<?php echo htmlspecialchars($_POST['app_url'] ?? (isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])),'/\\')); ?>">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-rocket me-2"></i>Install ClinicAll</button>
        </form>

        <?php elseif ($step === 5): ?>
        <div class="text-center py-3">
            <div style="font-size:3.5rem;" class="mb-3">&#9989;</div>
            <h4 class="fw-bold text-success">Installation Complete!</h4>
            <p class="text-muted">ClinicAll is ready to use.</p>
            <div class="d-grid gap-2 col-8 mx-auto mt-4">
                <a href="../index.php" class="btn btn-primary btn-lg">
                    <i class="fa fa-sign-in-alt me-2"></i>Go to Admin Login
                </a>
            </div>
            <div class="alert alert-warning mt-4 text-start">
                <strong><i class="fa fa-exclamation-triangle me-2"></i>Security:</strong>
                Delete or protect the <code>install/</code> directory after setup.
            </div>
        </div>
        <?php endif; ?>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
