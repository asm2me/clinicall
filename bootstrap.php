<?php
/**
 * ClinicAll — Bootstrap
 * Included by every entry-point (index.php, booking.php, ajax.php).
 */

define('CLINICALL_ROOT', __DIR__);
define('CLINICALL_VERSION', '2.0');

// ── Autoload core classes ─────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $file = CLINICALL_ROOT . '/core/' . $class . '.php';
    if (file_exists($file)) { require_once $file; }
});

require_once CLINICALL_ROOT . '/core/helpers.php';

// ── Error handling ────────────────────────────────────────────────────────
$cfg = require CLINICALL_ROOT . '/config.php';

if ($cfg['app']['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// ── Timezone ──────────────────────────────────────────────────────────────
date_default_timezone_set($cfg['app']['timezone']);

// ── Session ───────────────────────────────────────────────────────────────
$scfg = $cfg['session'];
session_name($scfg['name']);
session_set_cookie_params([
    'lifetime' => $scfg['lifetime'],
    'path'     => '/',
    'secure'   => $scfg['secure'],
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ── Database ──────────────────────────────────────────────────────────────
// If not installed yet, skip DB connect (install wizard handles its own connect)
if ($cfg['app']['installed']) {
    try {
        Database::connect($cfg['db']);
    } catch (Throwable $e) {
        if ($cfg['app']['debug']) {
            die('<pre>DB Error: ' . $e->getMessage() . '</pre>');
        }
        die('Database connection failed. Please check your configuration.');
    }
}

return $cfg;
