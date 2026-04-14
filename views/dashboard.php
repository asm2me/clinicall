<?php
Auth::requireLogin();
$page_title = 'Dashboard';
$tid = Auth::tenantId();
$today = date('Y-m-d');

// Stats cards
$stats = [];
if ($tid) {
    $stats['clinics']  = (int)Database::val("SELECT COUNT(*) FROM clinics WHERE tenant_id=:t AND enabled=".Database::bool(true), [':t'=>$tid]);
    $stats['doctors']  = (int)Database::val("SELECT COUNT(*) FROM doctors WHERE tenant_id=:t AND enabled=".Database::bool(true), [':t'=>$tid]);
    $stats['today']    = (int)Database::val("SELECT COUNT(*) FROM appointments WHERE tenant_id=:t AND appointment_date=:d", [':t'=>$tid,':d'=>$today]);
    $stats['pending']  = (int)Database::val("SELECT COUNT(*) FROM appointments WHERE tenant_id=:t AND status='pending'", [':t'=>$tid]);
    $stats['this_month'] = (int)Database::val("SELECT COUNT(*) FROM appointments WHERE tenant_id=:t AND DATE_TRUNC('month',appointment_date::date)=DATE_TRUNC('month',CURRENT_DATE)", [':t'=>$tid]);
} else {
    // Superadmin without tenant selected — system-wide
    $stats['tenants']  = (int)Database::val("SELECT COUNT(*) FROM tenants");
    $stats['clinics']  = (int)Database::val("SELECT COUNT(*) FROM clinics");
    $stats['doctors']  = (int)Database::val("SELECT COUNT(*) FROM doctors");
    $stats['today']    = (int)Database::val("SELECT COUNT(*) FROM appointments WHERE appointment_date=:d", [':d'=>$today]);
}

// Today's appointments
$today_appts = [];
if ($tid) {
    $today_appts = Database::all(
        "SELECT a.*, c.name AS clinic_name, d.name AS doctor_name
         FROM appointments a
         JOIN clinics c ON c.id = a.clinic_id
         JOIN doctors d ON d.id = a.doctor_id
         WHERE a.tenant_id = :t AND a.appointment_date = :d
         ORDER BY a.appointment_time",
        [':t' => $tid, ':d' => $today]
    );
}

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="dashboard-hero mb-4">
    <div class="dashboard-hero__content">
        <span class="dashboard-hero__eyebrow"><i class="fa fa-sparkles me-2"></i>Clinic overview</span>
        <h2 class="dashboard-hero__title">Welcome back<?php echo Auth::user() ? ', ' . e(Auth::user()['name']) : ''; ?></h2>
        <p class="dashboard-hero__subtitle">Track appointments, manage clinics, and keep the day running smoothly from one place.</p>
        <div class="dashboard-hero__actions">
            <?php if (Auth::can('appointment.create')): ?>
            <a href="?page=appointment_edit" class="btn btn-primary">
                <i class="fa fa-calendar-plus me-2"></i>New Appointment
            </a>
            <?php endif; ?>
            <?php if (Auth::can('doctor.create')): ?>
            <a href="?page=doctor_edit" class="btn btn-outline-secondary">
                <i class="fa fa-user-doctor me-2"></i>Add Doctor
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="dashboard-hero__art" aria-hidden="true">
        <div class="dashboard-orb dashboard-orb--lg"></div>
        <div class="dashboard-orb dashboard-orb--sm"></div>
        <div class="dashboard-hero__card">
            <div class="dashboard-hero__card-icon">
                <i class="fa fa-heart-pulse"></i>
            </div>
            <div>
                <div class="dashboard-hero__card-label">Today's focus</div>
                <div class="dashboard-hero__card-value"><?php echo (int)($stats['today'] ?? 0); ?> appointments</div>
            </div>
        </div>
    </div>
</div>

<!-- Stats cards -->
<div class="row g-3 mb-4">
    <?php if (!$tid && Auth::isSuperAdmin()): ?>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="stat-card__pattern" aria-hidden="true"></div>
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label">Tenants</p>
                        <h3 class="stat-value"><?php echo $stats['tenants']; ?></h3>
                    </div>
                    <div class="stat-icon bg-purple-soft"><i class="fa fa-building text-purple"></i></div>
                </div>
                <a href="?page=admin_tenants" class="stat-link">Manage <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="stat-label">Clinics</p>
                        <h3 class="stat-value"><?php echo $stats['clinics']; ?></h3>
                    </div>
                    <div class="stat-icon bg-blue-soft"><i class="fa fa-hospital text-blue"></i></div>
                </div>
                <a href="?page=clinics" class="stat-link">View <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="stat-label">Doctors</p>
                        <h3 class="stat-value"><?php echo $stats['doctors']; ?></h3>
                    </div>
                    <div class="stat-icon bg-teal-soft"><i class="fa fa-user-md text-teal"></i></div>
                </div>
                <a href="?page=doctors" class="stat-link">View <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="stat-label">Today's Appointments</p>
                        <h3 class="stat-value"><?php echo $stats['today']; ?></h3>
                    </div>
                    <div class="stat-icon bg-green-soft"><i class="fa fa-calendar-check text-green"></i></div>
                </div>
                <a href="?page=appointments&date=<?php echo $today; ?>" class="stat-link">View Today <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <?php if ($tid): ?>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="stat-label">Pending Approval</p>
                        <h3 class="stat-value text-warning"><?php echo $stats['pending']; ?></h3>
                    </div>
                    <div class="stat-icon bg-yellow-soft"><i class="fa fa-clock text-warning"></i></div>
                </div>
                <a href="?page=appointments&status=pending" class="stat-link">Review <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="stat-label">This Month</p>
                        <h3 class="stat-value"><?php echo $stats['this_month']; ?></h3>
                    </div>
                    <div class="stat-icon bg-indigo-soft"><i class="fa fa-chart-bar text-indigo"></i></div>
                </div>
                <a href="?page=appointments" class="stat-link">All <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Today's schedule -->
<?php if ($tid): ?>
<div class="row g-3 dashboard-grid">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm dashboard-panel">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="fa fa-calendar-day me-2"></i>Today's Appointments</h6>
                <a href="?page=appointment_edit" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i>New
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($today_appts)): ?>
                <div class="dashboard-empty text-center py-5 text-muted">
                    <div class="dashboard-empty__icon"><i class="fa fa-calendar fa-2x"></i></div>
                    <p class="mb-0">No appointments today.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Clinic</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($today_appts as $a): ?>
                        <tr>
                            <td class="fw-semibold"><?php echo substr($a['appointment_time'],0,5); ?></td>
                            <td>
                                <?php echo e($a['patient_name']); ?>
                                <br><small class="text-muted"><?php echo e($a['patient_phone']); ?></small>
                            </td>
                            <td><?php echo e($a['doctor_name']); ?></td>
                            <td><?php echo e($a['clinic_name']); ?></td>
                            <td><?php echo status_badge($a['status']); ?></td>
                            <td>
                                <a href="?page=appointment_edit&id=<?php echo e($a['id']); ?>"
                                   class="btn btn-xs btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm dashboard-panel dashboard-links-card">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fa fa-link me-2"></i>Quick Links</h6>
            </div>
            <div class="card-body">
                <div class="dashboard-quicklinks">
                    <?php if (Auth::can('appointment.create')): ?>
                    <a href="?page=appointment_edit" class="btn btn-outline-primary dashboard-quicklink text-start">
                        <i class="fa fa-plus me-2"></i>New Appointment
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('clinic.create')): ?>
                    <a href="?page=clinic_edit" class="btn btn-outline-secondary dashboard-quicklink text-start">
                        <i class="fa fa-hospital me-2"></i>Add Clinic
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('doctor.create')): ?>
                    <a href="?page=doctor_edit" class="btn btn-outline-secondary dashboard-quicklink text-start">
                        <i class="fa fa-user-md me-2"></i>Add Doctor
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('schedule.create')): ?>
                    <a href="?page=schedule_edit" class="btn btn-outline-secondary dashboard-quicklink text-start">
                        <i class="fa fa-clock me-2"></i>Add Schedule
                    </a>
                    <?php endif; ?>
                    <?php $t = Auth::tenant(); if ($t): ?>
                    <a href="booking.php?t=<?php echo e($t['slug']); ?>" target="_blank"
                       class="btn btn-outline-success dashboard-quicklink text-start">
                        <i class="fa fa-external-link me-2"></i>Open Booking Page
                    </a>
                    <?php endif; ?>
                </div>
                <div class="dashboard-side-illustration" aria-hidden="true">
                    <div class="dashboard-side-illustration__icon"><i class="fa fa-user-nurse"></i></div>
                    <div class="dashboard-side-illustration__pulse"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once CLINICALL_ROOT . '/views/layout/footer.php'; ?>
