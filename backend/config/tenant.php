<?php

return [
    'public_tables' => [
        'tenants',
        'domains',
        'plans',
        'subscriptions',
        'feature_flags',
    ],
    'tenant_tables' => [
        'users',
        'doctors',
        'patients',
        'appointments',
        'schedules',
        'invoices',
        'services',
        'settings',
        'website_pages',
    ],
    'middleware_aliases' => [
        'resolve' => 'tenant.resolve',
        'enforce' => 'tenant.enforce',
        'scope' => 'tenant.scope',
    ],
    'domain_patterns' => [
        'subdomain' => '{tenant}.platform.com',
        'custom_domain' => '{domain}',
    ],
];
