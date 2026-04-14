<?php
/**
 * ClinicAll — Non-interactive installer for Docker/CLI
 *
 * Expected environment variables:
 * DB_DRIVER, DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
 * APP_NAME, APP_URL, APP_DEBUG, APP_TZ
 * ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
 * ORG_NAME, ORG_SLUG
 */

define('CLINICALL_ROOT', __DIR__);
define('CLINICALL_VERSION', '2.0');

require_once CLINICALL_ROOT . '/core/helpers.php';
require_once CLINICALL_ROOT . '/core/Database.php';

$config_file = CLINICALL_ROOT . '/config.php';
$current_cfg = require $config_file;

if (($current_cfg['app']['installed'] ?? false) === true) {
    fwrite(STDOUT, "ClinicAll is already installed.\n");
    exit(0);
}

$dbDriver = getenv('DB_DRIVER') ?: 'pgsql';

$db = [
    'driver'   => $dbDriver,
    'host'     => getenv('DB_HOST') ?: 'db',
    'port'     => getenv('DB_PORT') ?: ($dbDriver === 'mysql' ? '3306' : '5432'),
    'name'     => getenv('DB_NAME') ?: 'clinicall',
    'user'     => getenv('DB_USER') ?: 'clinicall',
    'password' => getenv('DB_PASS') ?: '',
    'charset'  => 'utf8',
];

$app_name = getenv('APP_NAME') ?: 'ClinicAll';
$app_url = rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/');
$app_debug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN);
$app_debug_export = $app_debug ? 'true' : 'false';
$app_tz = getenv('APP_TZ') ?: 'UTC';

$org_name = trim(getenv('ORG_NAME') ?: 'Default Clinic');
$org_slug = strtolower(trim(getenv('ORG_SLUG') ?: preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', $org_name))));
$admin_name = trim(getenv('ADMIN_NAME') ?: 'Administrator');
$admin_email = strtolower(trim(getenv('ADMIN_EMAIL') ?: 'admin@example.com'));
$admin_password = getenv('ADMIN_PASSWORD') ?: 'ChangeMe123!';

if (!in_array($db['driver'], ['pgsql', 'mysql'], true)) {
    fwrite(STDERR, "Invalid DB_DRIVER. Use pgsql or mysql.\n");
    exit(1);
}

if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Invalid ADMIN_EMAIL.\n");
    exit(1);
}

if (strlen($admin_password) < 8) {
    fwrite(STDERR, "ADMIN_PASSWORD must be at least 8 characters.\n");
    exit(1);
}

$maxAttempts = 30;
$attempt = 0;

while (true) {
    try {
        Database::connect($db);
        Database::get()->query('SELECT 1');
        break;
    } catch (Throwable $e) {
        $attempt++;
        if ($attempt >= $maxAttempts) {
            fwrite(STDERR, "Database connection failed after {$maxAttempts} attempts: " . $e->getMessage() . "\n");
            exit(1);
        }
        fwrite(STDOUT, "Waiting for database ({$attempt}/{$maxAttempts})...\n");
        sleep(2);
    }
}

$sql_file = CLINICALL_ROOT . '/install/' . ($db['driver'] === 'mysql' ? 'mysql.sql' : 'pgsql.sql');
if (!file_exists($sql_file)) {
    fwrite(STDERR, "SQL file not found: {$sql_file}\n");
    exit(1);
}

$sql = file_get_contents($sql_file);

try {
    if ($db['driver'] === 'pgsql') {
        Database::get()->exec('CREATE EXTENSION IF NOT EXISTS pgcrypto;');
    }
} catch (Throwable $e) {
    fwrite(STDOUT, "Skipping pgcrypto extension setup: " . $e->getMessage() . "\n");
}

try {
    Database::get()->exec($sql);
} catch (Throwable $e) {
    $msg = $e->getMessage();
    $ignore =
        stripos($msg, 'already exists') !== false ||
        stripos($msg, 'duplicate key') !== false ||
        stripos($msg, 'Duplicate entry') !== false ||
        stripos($msg, 'multiple primary keys') !== false ||
        stripos($msg, 'already an index') !== false ||
        stripos($msg, 'duplicate column name') !== false ||
        stripos($msg, 'duplicate constraint') !== false;

    if (!$ignore) {
        fwrite(STDERR, "Schema error: {$msg}\n");
        exit(1);
    }
}

$tenantExists = Database::row(
    "SELECT id FROM tenants WHERE slug = :slug LIMIT 1",
    [':slug' => $org_slug ?: 'default']
);

if (!$tenantExists) {
    $tenant_id = generate_uuid();
    Database::insert('tenants', [
        'id'         => $tenant_id,
        'name'       => $org_name,
        'slug'       => $org_slug ?: 'default',
        'plan'       => 'standard',
        'enabled'    => true,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
} else {
    $tenant_id = $tenantExists['id'];
}

$userExists = Database::row(
    "SELECT id FROM users WHERE email = :email LIMIT 1",
    [':email' => $admin_email]
);

if (!$userExists) {
    Database::insert('users', [
        'id'         => generate_uuid(),
        'tenant_id'  => $tenant_id,
        'name'       => $admin_name,
        'email'      => $admin_email,
        'password'   => password_hash($admin_password, PASSWORD_BCRYPT, ['cost' => 12]),
        'role'       => 'superadmin',
        'enabled'    => true,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

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
        'name'      => '{$app_name}',
        'url'       => '{$app_url}',
        'debug'     => {$app_debug_export},
        'timezone'  => '{$app_tz}',
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

fwrite(STDOUT, "ClinicAll installation completed successfully.\n");
