<?php
/**
 * ClinicAll — Front Controller
 * All admin pages are routed through this file via ?page=xxx
 */

$cfg = require_once __DIR__ . '/bootstrap.php';

// Redirect to install wizard if not installed
if (!$cfg['app']['installed']) {
    header('Location: install/index.php');
    exit;
}

// Handle global POST actions (tenant switch, impersonation) before routing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])) {
    require_once __DIR__ . '/core/helpers.php';

    if ($_POST['_action'] === 'switch_tenant' && csrf_verify()) {
        $tid = clean_uuid($_POST['tenant_id'] ?? '');
        if ($tid && Auth::isSuperAdmin()) {
            Auth::switchTenant($tid);
            flash('Switched to tenant.');
        }
        redirect($_SERVER['HTTP_REFERER'] ?? '?page=dashboard');
    }

    if ($_POST['_action'] === 'impersonate_user' && csrf_verify()) {
        Auth::requireLogin();
        $uid = clean_uuid($_POST['user_id'] ?? '');
        if ($uid && Auth::impersonateUser($uid)) {
            flash('Logged in as selected user.');
            redirect('?page=dashboard');
        }

        flash('Unable to log in as that user.', 'danger');
        redirect($_SERVER['HTTP_REFERER'] ?? '?page=dashboard');
    }

    if ($_POST['_action'] === 'stop_impersonation' && csrf_verify()) {
        Auth::requireLogin();
        if (Auth::stopImpersonating()) {
            flash('Returned to your original account.');
        } else {
            flash('No impersonation session was active.', 'warning');
        }
        redirect($_SERVER['HTTP_REFERER'] ?? '?page=dashboard');
    }
}

// Route map: page slug → view file path
$routes = [
    // Auth
    'login'              => 'views/login.php',
    'logout'             => null,  // handled inline below
    'change_password'    => 'views/change_password.php',

    // Main
    'dashboard'          => 'views/dashboard.php',

    // Clinics
    'clinics'            => 'views/clinics.php',
    'clinic_edit'        => 'views/clinic_edit.php',

    // Doctors
    'doctors'            => 'views/doctors.php',
    'doctor_edit'        => 'views/doctor_edit.php',

    // Schedules
    'schedules'          => 'views/schedules.php',
    'schedule_edit'      => 'views/schedule_edit.php',
    'exceptions'         => 'views/exceptions.php',

    // Appointments
    'appointments'       => 'views/appointment_edit.php',  // handled below
    'appointment_edit'   => 'views/appointment_edit.php',

    // Admin
    'admin_tenants'      => 'views/admin/tenants.php',
    'admin_tenant_edit'  => 'views/admin/tenant_edit.php',
    'admin_users'        => 'views/admin/users.php',
    'admin_user_edit'    => 'views/admin/user_edit.php',
];

// Fix: appointments list vs edit
$routes['appointments'] = 'views/appointments.php';

$page = preg_replace('/[^a-z0-9_]/', '', strtolower((string)($_GET['page'] ?? 'dashboard')));

// Logout
if ($page === 'logout') {
    if (Auth::check()) { Auth::logout(); }
    redirect('?page=login');
}

// Login page (no auth needed)
if ($page === 'login') {
    require_once __DIR__ . '/views/login.php';
    exit;
}

// Everything else requires login
Auth::requireLogin();

// Resolve view file
$view = isset($routes[$page]) ? __DIR__ . '/' . $routes[$page] : null;

if (!$view || !file_exists($view)) {
    flash("Page '$page' not found.", 'warning');
    redirect('?page=dashboard');
}

require_once $view;
