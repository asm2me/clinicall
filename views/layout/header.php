<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'ClinicAll'); ?> — <?php echo e($cfg['app']['name']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo e($cfg['app']['url']); ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="clinicall-admin">

<?php $user = Auth::user(); $tenant = Auth::tenant(); ?>

<?php if (Auth::check()): ?>
<nav class="classic-topbar">
    <div class="container-fluid classic-topbar-inner">
        <div class="d-flex align-items-center gap-3">
            <a class="classic-brand" href="?page=dashboard">
                <i class="fa fa-hospital me-2"></i><?php echo e($cfg['app']['name']); ?>
            </a>

            <?php if ($tenant): ?>
            <span class="classic-tenant-pill d-none d-md-inline-flex">
                <i class="fa fa-building me-2"></i><?php echo e($tenant['name']); ?>
            </span>
            <?php elseif (Auth::isSuperAdmin()): ?>
            <span class="classic-tenant-pill d-none d-md-inline-flex">
                <i class="fa fa-star me-2"></i>Superadmin
            </span>
            <?php endif; ?>
        </div>

        <button type="button" class="classic-menu-toggle d-lg-none" data-sidebar-toggle aria-expanded="false" aria-controls="classic-sidebar">
            <i class="fa fa-bars"></i>
            <span>Menu</span>
        </button>

        <div class="classic-topbar-actions">
            <button type="button" class="theme-toggle" data-theme-toggle aria-pressed="false">
                <i class="fa fa-moon" data-theme-icon></i>
                <span class="theme-toggle-label" data-theme-label>Classic</span>
            </button>

            <?php if (Auth::isImpersonating()): ?>
            <form method="post" class="m-0">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_action" value="stop_impersonation">
                <button type="submit" class="btn btn-sm btn-warning">
                    <i class="fa fa-rotate-left me-1"></i>Back to Admin
                </button>
            </form>
            <?php endif; ?>

            <?php if (Auth::isSuperAdmin()): ?>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fa fa-exchange-alt me-1"></i>Switch Tenant
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="max-height:300px;overflow-y:auto;">
                    <?php
                    $all_tenants = Database::all("SELECT id, name, slug FROM tenants WHERE enabled = " . Database::bool(true) . " ORDER BY name");
                    foreach ($all_tenants as $t): ?>
                    <li>
                        <form method="post" class="m-0">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="_action" value="switch_tenant">
                            <input type="hidden" name="tenant_id" value="<?php echo e($t['id']); ?>">
                            <button type="submit" class="dropdown-item <?php echo ($tenant && $tenant['id'] === $t['id']) ? 'active' : ''; ?>">
                                <?php echo e($t['name']); ?>
                                <small class="text-muted">(<?php echo e($t['slug']); ?>)</small>
                            </button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <a href="?page=change_password" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex">
                <i class="fa fa-key me-1"></i>Change Password
            </a>
            <a href="?page=logout" class="btn btn-sm btn-danger d-none d-md-inline-flex">
                <i class="fa fa-sign-out-alt me-1"></i>Logout
            </a>

            <div class="dropdown">
                <a class="classic-user" href="#" data-bs-toggle="dropdown">
                    <span class="classic-user-avatar">
                        <i class="fa fa-user"></i>
                    </span>
                    <span class="classic-user-meta d-none d-sm-flex">
                        <strong><?php echo e($user['name']); ?></strong>
                        <small><?php echo e(ucfirst($user['role'])); ?></small>
                    </span>
                    <i class="fa fa-chevron-down classic-user-caret"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-muted small"><?php echo e($user['email']); ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="?page=dashboard"><i class="fa fa-gauge me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item" href="?page=change_password"><i class="fa fa-key me-2"></i>Change Password</a></li>
                    <li><a class="dropdown-item text-danger" href="?page=logout"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="classic-shell">
    <aside class="classic-sidebar" id="classic-sidebar">
        <div class="classic-sidebar-title">Navigation</div>
        <ul class="classic-sidebar-nav">
            <li>
                <a class="<?php echo ($page ?? '') === 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                    <i class="fa fa-gauge me-2"></i>Dashboard
                </a>
            </li>

            <?php if (Auth::can('clinic.view')): ?>
            <li>
                <a class="<?php echo in_array($page ?? '', ['clinics', 'clinic_edit']) ? 'active' : ''; ?>" href="?page=clinics">
                    <i class="fa fa-hospital me-2"></i>Clinics
                </a>
            </li>
            <?php endif; ?>

            <?php if (Auth::can('doctor.view')): ?>
            <li>
                <a class="<?php echo in_array($page ?? '', ['doctors', 'doctor_edit']) ? 'active' : ''; ?>" href="?page=doctors">
                    <i class="fa fa-user-md me-2"></i>Doctors
                </a>
            </li>
            <?php endif; ?>

            <?php if (Auth::can('schedule.view')): ?>
            <li>
                <a class="<?php echo in_array($page ?? '', ['schedules', 'schedule_edit', 'exceptions']) ? 'active' : ''; ?>" href="?page=schedules">
                    <i class="fa fa-calendar-week me-2"></i>Schedules
                </a>
            </li>
            <?php endif; ?>

            <?php if (Auth::can('appointment.view')): ?>
            <li>
                <a class="<?php echo in_array($page ?? '', ['appointments', 'appointment_edit']) ? 'active' : ''; ?>" href="?page=appointments">
                    <i class="fa fa-calendar-check me-2"></i>Appointments
                </a>
            </li>
            <?php endif; ?>

            <?php if (Auth::isSuperAdmin()): ?>
            <li>
                <a class="<?php echo in_array($page ?? '', ['admin_tenants', 'admin_tenant_edit']) ? 'active' : ''; ?>" href="?page=admin_tenants">
                    <i class="fa fa-building me-2"></i>Tenants
                </a>
            </li>
            <li>
                <a class="<?php echo in_array($page ?? '', ['admin_users', 'admin_user_edit']) ? 'active' : ''; ?>" href="?page=admin_users">
                    <i class="fa fa-users me-2"></i>Users
                </a>
            </li>
            <?php elseif (Auth::can('user.view')): ?>
            <li>
                <a class="<?php echo in_array($page ?? '', ['admin_users', 'admin_user_edit']) ? 'active' : ''; ?>" href="?page=admin_users">
                    <i class="fa fa-users me-2"></i>Users
                </a>
            </li>
            <?php endif; ?>

            <?php if ($tenant): ?>
            <li>
                <a href="booking.php?t=<?php echo e($tenant['slug']); ?>" target="_blank">
                    <i class="fa fa-arrow-up-right-from-square me-2"></i>Booking Page
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </aside>

    <main class="classic-content">
        <div class="classic-page-head">
            <div>
                <h1 class="classic-page-title"><?php echo e($page_title ?? 'Dashboard'); ?></h1>
                <div class="classic-page-subtitle"><?php echo date('l, d F Y'); ?></div>
            </div>
        </div>

        <?php echo flash_html(); ?>
<?php else: ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="?page=dashboard">
            <i class="fa fa-hospital me-2"></i><?php echo e($cfg['app']['name']); ?>
        </a>
    </div>
</nav>

<div class="container-fluid py-4 px-4">
    <?php echo flash_html(); ?>
<?php endif; ?>
