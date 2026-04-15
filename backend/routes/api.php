<?php

return [
    'base_path' => '/api/v1',
    'health' => '/api/v1/health',
    'middleware' => [
        'tenant.resolve',
        'rbac.api',
    ],
    'tenant_scoped_prefix' => '/api/v1/tenant',
    'tenant_scoped_routes' => [
        'appointments' => '/appointments',
        'website_pages' => '/website-pages',
        'doctors' => '/doctors',
        'patients' => '/patients',
    ],
];
