#!/bin/sh
set -e

if [ ! -f /var/www/html/config.php ]; then
  cat > /var/www/html/config.php <<PHP
<?php
return [
    'db' => [
        'driver'   => getenv('DB_DRIVER') ?: 'pgsql',
        'host'     => getenv('DB_HOST') ?: 'db',
        'port'     => getenv('DB_PORT') ?: '5432',
        'name'     => getenv('DB_NAME') ?: 'clinicall',
        'user'     => getenv('DB_USER') ?: 'clinicall',
        'password' => getenv('DB_PASS') ?: '',
        'charset'  => 'utf8',
    ],
    'app' => [
        'name'      => getenv('APP_NAME') ?: 'ClinicAll',
        'url'       => getenv('APP_URL') ?: 'http://localhost',
        'debug'     => filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN),
        'timezone'  => getenv('APP_TZ') ?: 'UTC',
        'installed' => false,
    ],
    'session' => [
        'name'     => 'clinicall_sess',
        'lifetime' => 7200,
        'secure'   => filter_var(getenv('SESSION_SECURE') ?: '0', FILTER_VALIDATE_BOOLEAN),
    ],
];
PHP
fi

php /var/www/html/install-cli.php

exec php-fpm -F
