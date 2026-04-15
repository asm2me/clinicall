<?php

return [
    'roles' => [
        'super_admin',
        'clinic_admin',
        'doctor',
        'receptionist',
        'patient',
    ],
    'permissions' => [
        'tenant.manage',
        'clinic.manage',
        'doctor.manage',
        'patient.manage',
        'appointment.manage',
        'invoice.manage',
        'website.manage',
        'billing.manage',
    ],
    'guards' => [
        'api' => [
            'middleware' => [
                'rbac.api',
            ],
        ],
        'web' => [
            'middleware' => [
                'rbac.web',
            ],
        ],
    ],
];
