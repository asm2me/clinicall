<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'ClinicAll'); ?> — <?php echo e($cfg['app']['name']); ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- ClinicAll custom CSS -->
    <link href="<?php echo e($cfg['app']['url']); ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="clinicall-admin">

<?php $user = Auth::user(); $tenant = Auth::tenant(); ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="?page=dashboard">
            <i class="fa fa-hospital me-2"></i><?php echo e($cfg['app']['name']); ?>
        </a>

        <?php if (Auth::check()): ?>
        <!-- Tenant badge -->
        <span class="badge bg-white text-primary ms-2 d-none d-md-inline">
            <?php if (Auth::isSuperAdmin() && $tenant): ?>
                <i class="fa fa-building me-1"></i><?php echo e($tenant['name']); ?>
            <?php elseif ($tenant): ?>
                <i class="fa fa-building me-1"></i><?php echo e($tenant['name']); ?>
            <?php else: ?>
                <i class="fa fa-star me-1"></i>Superadmin
            <?php endif; ?>
        </span>
        <?php endif; ?>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <?php if (Auth::check()): ?>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($page??'')==='dashboard' ? 'active' : ''; ?>"
                       href="?page=dashboard"><i class="fa fa-gauge me-1"></i>Dashboard</a>
                </li>

                <?php if (Auth::can('clinic.view')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($page??'', ['clinics','clinic_edit','doctors','doctor_edit']) ? 'active' : ''; ?>"
                       href="#" data-bs-toggle="dropdown">
                        <i class="fa fa-hospital me-1"></i>Clinics
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?page=clinics">
                            <i class="fa fa-list me-2"></i>All Clinics</a></li>
                        <?php if (Auth::can('clinic.create')): ?>
                        <li><a class="dropdown-item" href="?page=clinic_edit">
                            <i class="fa fa-plus me-2"></i>Add Clinic</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?page=doctors">
                            <i class="fa fa-user-md me-2"></i>All Doctors</a></li>
                        <?php if (Auth::can('doctor.create')): ?>
                        <li><a class="dropdown-item" href="?page=doctor_edit">
                            <i class="fa fa-plus me-2"></i>Add Doctor</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (Auth::can('schedule.view')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($page??'', ['schedules','schedule_edit','exceptions']) ? 'active' : ''; ?>"
                       href="#" data-bs-toggle="dropdown">
                        <i class="fa fa-calendar-week me-1"></i>Schedules
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?page=schedules">
                            <i class="fa fa-clock me-2"></i>Weekly Schedules</a></li>
                        <li><a class="dropdown-item" href="?page=exceptions">
                            <i class="fa fa-calendar-xmark me-2"></i>Exceptions / Days Off</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (Auth::can('appointment.view')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($page??'', ['appointments','appointment_edit']) ? 'active' : ''; ?>"
                       href="?page=appointments">
                        <i class="fa fa-calendar-check me-1"></i>Appointments
                    </a>
                </li>
                <?php endif; ?>

                <?php if (Auth::isSuperAdmin()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($page??'', ['admin_tenants','admin_tenant_edit','admin_users','admin_user_edit']) ? 'active' : ''; ?>"
                       href="#" data-bs-toggle="dropdown">
                        <i class="fa fa-cog me-1"></i>Admin
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?page=admin_tenants">
                            <i class="fa fa-building me-2"></i>Tenants</a></li>
                        <li><a class="dropdown-item" href="?page=admin_users">
                            <i class="fa fa-users me-2"></i>All Users</a></li>
                    </ul>
                </li>
                <?php elseif (Auth::can('user.view')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($page??'', ['admin_users','admin_user_edit']) ? 'active' : ''; ?>"
                       href="?page=admin_users">
                        <i class="fa fa-users me-1"></i>Users
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Right side -->
            <ul class="navbar-nav ms-auto">
                <!-- Booking link -->
                <?php if ($tenant): ?>
                <li class="nav-item">
                    <a class="nav-link" href="booking.php?t=<?php echo e($tenant['slug']); ?>" target="_blank">
                        <i class="fa fa-external-link me-1"></i>Booking Page
                    </a>
                </li>
                <?php endif; ?>

                <!-- Superadmin: switch tenant -->
                <?php if (Auth::isSuperAdmin()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fa fa-exchange-alt me-1"></i>Switch Tenant
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="max-height:300px;overflow-y:auto;">
                        <?php
                        $all_tenants = Database::all("SELECT id, name, slug FROM tenants WHERE enabled = " . Database::bool(true) . " ORDER BY name");
                        foreach ($all_tenants as $t): ?>
                        <li>
                            <form method="post" style="margin:0;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="_action" value="switch_tenant">
                                <input type="hidden" name="tenant_id" value="<?php echo e($t['id']); ?>">
                                <button type="submit" class="dropdown-item <?php echo ($tenant && $tenant['id']===$t['id']) ? 'active' : ''; ?>">
                                    <?php echo e($t['name']); ?>
                                    <small class="text-muted">(<?php echo e($t['slug']); ?>)</small>
                                </button>
                            </form>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fa fa-user-circle me-1"></i><?php echo e($user['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text text-muted small"><?php echo e($user['email']); ?></span></li>
                        <li><span class="dropdown-item-text text-muted small">Role: <?php echo ucfirst($user['role']); ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="?page=logout">
                            <i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</nav>

<!-- Main content wrapper -->
<div class="container-fluid py-4 px-4">

    <?php echo flash_html(); ?>
