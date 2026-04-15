<?php

declare(strict_types=1);

return [
    'resolver' => [
        'header' => 'X-Tenant-Id',
        'query' => 'tenant_id',
        'route' => 'tenant',
    ],
    'model' => App\Models\Tenant::class,
    'statuses' => [
        'active',
        'inactive',
        'suspended',
    ],
];