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
<body class="clinicall-admin theme-ample">

<?php $user = Auth::user(); $tenant = Auth::tenant(); ?>

<?php if (Auth::check()): ?>
<div class="ample-shell">
    <aside class="ample-sidebar">
        <div class="ample-brand">
            <a class="navbar-brand fw-bold m-0" href="?page=dashboard">
                <i class="fa fa-hospital me-2"></i><?php echo e($cfg['app']['name']); ?>
            </a>
        </div>

        <div class="ample-sidebar-section">
            <div class="ample-sidebar-label">Navigation</div>
            <ul class="ample-nav">
                <li>
                    <a class="<?php echo ($page??'')==='dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                        <i class="fa fa-gauge"></i><span>Dashboard</span>
                    </a>
                </li>

                <?php if (Auth::can('clinic.view')): ?>
                <li>
                    <a class="<?php echo in_array($page??'', ['clinics','clinic_edit']) ? 'active' : ''; ?>" href="?page=clinics">
                        <i class="fa fa-hospital"></i><span>Clinics</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (Auth::can('doctor.view')): ?>
                <li>
                    <a class="<?php echo in_array($page??'', ['doctors','doctor_edit']) ? 'active' : ''; ?>" href="?page=doctors">
                        <i class="fa fa-user-md"></i><span>Doctors</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (Auth::can('schedule.view')): ?>
                <li>
                    <a class="<?php echo in_array($page??'', ['schedules','schedule_edit','exceptions']) ? 'active' : ''; ?>" href="?page=schedules">
                        <i class="fa fa-calendar-week"></i><span>Schedules</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (Auth::can('appointment.view')): ?>
                <li>
                    <a class="<?php echo in_array($page??'', ['appointments','appointment_edit']) ? 'active' : ''; ?>" href="?page=appointments">
                        <i class="fa fa-calendar-check"></i><span>Appointments</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (Auth::isSuperAdmin()): ?>
                <li>
                    <a class="<?php echo in_array($page??'', ['admin_tenants']) ? 'active' : ''; ?>" href="?page=admin_tenants">
                        <i class="fa fa-building"></i><span>Tenants</span>
                    </a>
                </li>
                <li>
                    <a class="<?php echo in_array($page??'', ['admin_users','admin_user_edit']) ? 'active' : ''; ?>" href="?page=admin_users">
                        <i class="fa fa-users"></i><span>Users</span>
                    </a>
                </li>
                <?php elseif (Auth::can('user.view')): ?>
                <li>
                    <a class="<?php echo in_array($page??'', ['admin_users','admin_user_edit']) ? 'active' : ''; ?>" href="?page=admin_users">
                        <i class="fa fa-users"></i><span>Users</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($tenant): ?>
                <li>
                    <a href="booking.php?t=<?php echo e($tenant['slug']); ?>" target="_blank">
                        <i class="fa fa-arrow-up-right-from-square"></i><span>Booking Page</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="ample-sidebar-promo">
            <a href="?page=change_password" class="btn btn-danger w-100 mb-2">
                <i class="fa fa-key me-1"></i>Change Password
            </a>
            <a href="?page=logout" class="btn btn-outline-secondary w-100">
                <i class="fa fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </aside>

    <div class="ample-main">
        <nav class="ample-topbar">
            <div class="ample-topbar-left">
                <h1 class="ample-page-title"><?php echo e($page_title ?? 'Dashboard'); ?></h1>
                <div class="ample-breadcrumb">Dashboard</div>
            </div>

            <div class="ample-topbar-right">
                <?php if (Auth::isImpersonating()): ?>
                <form method="post" class="d-inline-block">
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
                                <button type="submit" class="dropdown-item <?php echo ($tenant && $tenant['id']===$t['id']) ? 'active' : ''; ?>">
                                    <?php echo e($t['name']); ?>
                                    <small class="text-muted">(<?php echo e($t['slug']); ?>)</small>
                                </button>
                            </form>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="ample-search">
                    <i class="fa fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>

                <?php if (Auth::check()): ?>
                <span class="badge bg-white text-primary d-none d-md-inline">
                    <?php if (Auth::isSuperAdmin() && $tenant): ?>
                        <i class="fa fa-building me-1"></i><?php echo e($tenant['name']); ?>
                    <?php elseif ($tenant): ?>
                        <i class="fa fa-building me-1"></i><?php echo e($tenant['name']); ?>
                    <?php else: ?>
                        <i class="fa fa-star me-1"></i>Superadmin
                    <?php endif; ?>
                </span>
                <?php endif; ?>

                <div class="dropdown">
                    <a class="ample-user" href="#" data-bs-toggle="dropdown">
                        <span class="ample-user-avatar"><i class="fa fa-user"></i></span>
                        <span class="ample-user-name"><?php echo e($user['name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text text-muted small"><?php echo e($user['email']); ?></span></li>
                        <li><span class="dropdown-item-text text-muted small">Role: <?php echo ucfirst($user['role']); ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?page=dashboard"><i class="fa fa-gauge me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="?page=change_password"><i class="fa fa-key me-2"></i>Change Password</a></li>
                        <li><a class="dropdown-item text-danger" href="?page=logout"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="ample-content">
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
