<?php

declare(strict_types=1);

return [
    'roles' => [
        'super_admin' => [
            'label' => 'Super Admin',
            'scope' => 'platform',
            'permissions' => ['*'],
        ],
        'clinic_admin' => [
            'label' => 'Clinic Admin',
            'scope' => 'tenant',
            'permissions' => ['tenant.manage', 'appointments.manage', 'patients.manage', 'staff.manage'],
        ],
        'doctor' => [
            'label' => 'Doctor',
            'scope' => 'tenant',
            'permissions' => ['appointments.view', 'patients.view', 'schedule.manage'],
        ],
        'receptionist' => [
            'label' => 'Receptionist',
            'scope' => 'tenant',
            'permissions' => ['appointments.manage', 'patients.manage'],
        ],
        'patient' => [
            'label' => 'Patient',
            'scope' => 'tenant',
            'permissions' => ['appointments.book', 'appointments.view'],
        ],
    ],
];