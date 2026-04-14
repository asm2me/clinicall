<?php
/**
 * ClinicAll — Standalone Multi-tenant Configuration
 *
 * After running the install wizard this file is regenerated with your
 * actual credentials.  You can also set values via environment variables;
 * env vars take precedence over the values below.
 */

return [

    // ── Database ──────────────────────────────────────────────────────────
    'db' => [
        'driver'   => getenv('DB_DRIVER')   ?: 'pgsql',   // 'pgsql' or 'mysql'
        'host'     => getenv('DB_HOST')     ?: '127.0.0.1',
        'port'     => getenv('DB_PORT')     ?: '5432',
        'name'     => getenv('DB_NAME')     ?: 'clinicall',
        'user'     => getenv('DB_USER')     ?: 'clinicall',
        'password' => getenv('DB_PASS')     ?: '',
        'charset'  => 'utf8',
    ],

    // ── Application ───────────────────────────────────────────────────────
    'app' => [
        'name'     => getenv('APP_NAME')  ?: 'ClinicAll',
        'url'      => getenv('APP_URL')   ?: 'http://localhost/clinicall',
        'debug'    => filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN),
        'timezone' => getenv('APP_TZ')    ?: 'UTC',
        'installed'=> false,              // set to true after install wizard
    ],

    // ── Session ───────────────────────────────────────────────────────────
    'session' => [
        'name'     => 'clinicall_sess',
        'lifetime' => 7200,              // seconds
        'secure'   => false,             // set true when behind HTTPS
    ],

];
