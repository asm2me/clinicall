<?php
/**
 * ClinicAll — Public Patient Booking Wizard
 * No authentication required. Accessible at: booking.php?t=tenant-slug
 */

$cfg = require_once __DIR__ . '/bootstrap.php';

if (!$cfg['app']['installed']) {
    die('<h2>ClinicAll is not set up yet.</h2>');
}

// ── Resolve tenant ────────────────────────────────────────────────────────
$tenant = null;
$slug   = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)($_GET['t'] ?? '')));

if ($slug) {
    $tenant = resolve_tenant_by_slug($slug);
}
if (!$tenant) {
    // Try custom domain
    $host = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $_SERVER['HTTP_HOST'] ?? '');
    if ($host) { $tenant = resolve_tenant_by_host($host); }
}
if (!$tenant) {
    // Fall back to first active tenant (single-tenant installs)
    $tenant = Database::row("SELECT * FROM tenants WHERE enabled=".Database::bool(true)." ORDER BY name LIMIT 1");
}

// Wizard state from URL params
$clinic_id = clean_uuid($_GET['clinic'] ?? '');
$doctor_id = clean_uuid($_GET['doctor'] ?? '');
$appt_date = preg_replace('/[^0-9\-]/', '', (string)($_GET['date'] ?? ''));
$appt_time = preg_replace('/[^0-9\:]/', '', (string)($_GET['time'] ?? ''));

$booked_ref = null;
$errors     = [];

// ── Handle booking POST ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tenant) {
    if (!csrf_verify()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $p_clinic = clean_uuid($_POST['clinic_id'] ?? '');
        $p_doctor = clean_uuid($_POST['doctor_id'] ?? '');
        $p_date   = preg_replace('/[^0-9\-]/', '', (string)($_POST['appt_date'] ?? ''));
        $p_time   = preg_replace('/[^0-9\:]/', '', (string)($_POST['appt_time'] ?? ''));
        $p_name   = trim($_POST['patient_name']  ?? '');
        $p_phone  = trim($_POST['patient_phone'] ?? '');
        $p_email  = trim($_POST['patient_email'] ?? '');
        $p_dob    = trim($_POST['patient_dob']   ?? '');
        $p_notes  = trim($_POST['patient_notes'] ?? '');

        if (!$p_clinic) { $errors[] = 'Clinic is required.'; }
        if (!$p_doctor) { $errors[] = 'Doctor is required.'; }
        if (!$p_date)   { $errors[] = 'Date is required.'; }
        if (!$p_time)   { $errors[] = 'Time slot is required.'; }
        if (!$p_name)   { $errors[] = 'Your name is required.'; }
        if (!$p_phone)  { $errors[] = 'Your phone number is required.'; }

        if (empty($errors)) {
            $avail = available_slots($tenant['id'], $p_doctor, $p_date);
            if (!in_array($p_time, $avail, true)) {
                $errors[] = 'Sorry, that slot is no longer available. Please select another.';
            }
        }

        if (empty($errors)) {
            $dow      = (int)date('w', strtotime($p_date));
            $sched_row= Database::row(
                "SELECT slot_duration FROM doctor_schedules WHERE tenant_id=:t AND doctor_id=:d AND day_of_week=:dow AND enabled=".Database::bool(true)." LIMIT 1",
                [':t'=>$tenant['id'],':d'=>$p_doctor,':dow'=>$dow]
            );
            $duration = $sched_row ? (int)$sched_row['slot_duration'] : 15;

            $new_id = generate_uuid();
            Database::insert('appointments', [
                'id'               => $new_id,
                'tenant_id'        => $tenant['id'],
                'clinic_id'        => $p_clinic,
                'doctor_id'        => $p_doctor,
                'patient_name'     => $p_name,
                'patient_phone'    => $p_phone,
                'patient_email'    => $p_email ?: null,
                'patient_dob'      => $p_dob   ?: null,
                'patient_notes'    => $p_notes ?: null,
                'appointment_date' => $p_date,
                'appointment_time' => $p_time,
                'duration'         => $duration,
                'status'           => 'pending',
                'type'             => 'general',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);
            $booked_ref = strtoupper(substr($new_id, 0, 8));
        }
    }
}

// ── Load data for current step ────────────────────────────────────────────
$clinics     = [];
$doctors     = [];
$slots       = [];
$sel_clinic  = null;
$sel_doctor  = null;

if ($tenant) {
    $clinics = Database::all(
        "SELECT id, name, address, phone FROM clinics WHERE tenant_id=:t AND enabled=".Database::bool(true)." ORDER BY name",
        [':t' => $tenant['id']]
    );
    if ($clinic_id) {
        foreach ($clinics as $c) { if ($c['id']===$clinic_id) { $sel_clinic=$c; break; } }
        $doctors = Database::all(
            "SELECT id, name, specialty, bio FROM doctors WHERE tenant_id=:t AND clinic_id=:c AND enabled=".Database::bool(true)." ORDER BY name",
            [':t'=>$tenant['id'],':c'=>$clinic_id]
        );
    }
    if ($doctor_id) {
        foreach ($doctors as $d) { if ($d['id']===$doctor_id) { $sel_doctor=$d; break; } }
        if ($appt_date) {
            $slots = available_slots($tenant['id'], $doctor_id, $appt_date);
        }
    }
}

$app_url = $cfg['app']['url'];
$app_name= $cfg['app']['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment — <?php echo e($tenant['name'] ?? $app_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo e($app_url); ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary-custom">
    <div class="container">
        <span class="navbar-brand fw-bold">
            <i class="fa fa-hospital me-2"></i>
            <?php echo e($tenant['name'] ?? $app_name); ?>
        </span>
        <span class="text-white-50 small d-none d-sm-inline">Online Appointment Booking</span>
    </div>
</nav>

<div class="container py-4" style="max-width:760px;">

<?php if (!$tenant): ?>
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle me-2"></i>
        Booking system not found. Please check the link you were provided.
    </div>

<?php elseif ($booked_ref): ?>
    <!-- SUCCESS -->
    <div class="card border-0 shadow text-center">
        <div class="card-body py-5">
            <div style="font-size:4rem;" class="mb-3">&#9989;</div>
            <h2 class="fw-bold text-success mb-2">Appointment Booked!</h2>
            <p class="text-muted mb-4">
                Your request has been submitted and is <strong>pending confirmation</strong>.<br>
                The clinic will contact you to confirm your appointment.
            </p>
            <table class="table table-bordered mx-auto" style="max-width:400px;">
                <tr><th class="text-start text-muted">Reference</th><td class="fw-bold text-primary"><?php echo e($booked_ref); ?></td></tr>
                <tr><th class="text-start text-muted">Patient</th><td><?php echo e($_POST['patient_name']??''); ?></td></tr>
                <tr><th class="text-start text-muted">Phone</th><td><?php echo e($_POST['patient_phone']??''); ?></td></tr>
                <tr><th class="text-start text-muted">Date</th><td><?php echo fmt_date($_POST['appt_date']??'','l, d F Y'); ?></td></tr>
                <tr><th class="text-start text-muted">Time</th><td><?php echo e($_POST['appt_time']??''); ?></td></tr>
            </table>
            <a href="booking.php?t=<?php echo e($slug); ?>" class="btn btn-primary mt-3">
                <i class="fa fa-plus me-2"></i>Book Another Appointment
            </a>
        </div>
    </div>

<?php else: ?>

    <!-- Progress steps -->
    <?php
    $step = 1;
    if ($clinic_id) $step = 2;
    if ($clinic_id && $doctor_id) $step = 3;
    if ($clinic_id && $doctor_id && $appt_date && $appt_time) $step = 4;
    $steps = ['Choose Clinic','Choose Doctor','Pick a Slot','Your Details'];
    ?>
    <div class="d-flex mb-4">
        <?php foreach ($steps as $i => $label): ?>
        <div class="flex-fill text-center px-1">
            <div class="step-indicator <?php echo $step > $i+1 ? 'done' : ($step === $i+1 ? 'active' : ''); ?>">
                <?php echo $step > $i+1 ? '✓' : ($i+1); ?>
            </div>
            <div class="small mt-1 <?php echo $step === $i+1 ? 'fw-semibold text-primary' : 'text-muted'; ?>">
                <?php echo $label; ?>
            </div>
        </div>
        <?php if ($i < 3): ?>
        <div class="step-line mt-3"></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo e($e); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <!-- STEP 1: Choose Clinic -->
    <?php if (!$clinic_id): ?>
    <h5 class="mb-3"><i class="fa fa-hospital me-2 text-primary"></i>Choose a Clinic</h5>
    <?php if (empty($clinics)): ?>
    <div class="alert alert-info">No clinics are available at this time. Please contact us directly.</div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($clinics as $c): ?>
        <div class="col-md-6">
            <a href="booking.php?t=<?php echo e($slug); ?>&clinic=<?php echo e($c['id']); ?>" class="text-decoration-none">
                <div class="card booking-option-card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1"><i class="fa fa-building me-2 text-primary"></i><?php echo e($c['name']); ?></h6>
                        <?php if ($c['address']): ?><p class="small text-muted mb-1"><i class="fa fa-map-marker-alt me-1"></i><?php echo e($c['address']); ?></p><?php endif; ?>
                        <?php if ($c['phone']): ?><p class="small text-muted mb-0"><i class="fa fa-phone me-1"></i><?php echo e($c['phone']); ?></p><?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- STEP 2: Choose Doctor -->
    <?php elseif (!$doctor_id): ?>
    <div class="mb-3">
        <a href="booking.php?t=<?php echo e($slug); ?>" class="text-decoration-none small">
            <i class="fa fa-arrow-left me-1"></i>Change clinic
        </a>
    </div>
    <h5 class="mb-3"><i class="fa fa-user-md me-2 text-primary"></i>Choose a Doctor at <?php echo e($sel_clinic['name']??''); ?></h5>
    <?php if (empty($doctors)): ?>
    <div class="alert alert-info">No doctors available at this clinic. Please try another or contact us.</div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($doctors as $d): ?>
        <div class="col-md-6">
            <a href="booking.php?t=<?php echo e($slug); ?>&clinic=<?php echo e($clinic_id); ?>&doctor=<?php echo e($d['id']); ?>" class="text-decoration-none">
                <div class="card booking-option-card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1"><i class="fa fa-user-md me-2 text-primary"></i><?php echo e($d['name']); ?></h6>
                        <?php if ($d['specialty']): ?><p class="small text-muted mb-1"><?php echo e($d['specialty']); ?></p><?php endif; ?>
                        <?php if ($d['bio']): ?><p class="small text-muted mb-0"><?php echo e(mb_substr($d['bio'],0,100)).(mb_strlen($d['bio'])>100?'…':''); ?></p><?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- STEP 3: Pick Date & Slot -->
    <?php elseif (!$appt_time): ?>
    <div class="mb-3">
        <a href="booking.php?t=<?php echo e($slug); ?>&clinic=<?php echo e($clinic_id); ?>" class="text-decoration-none small">
            <i class="fa fa-arrow-left me-1"></i>Change doctor
        </a>
    </div>
    <h5 class="mb-3"><i class="fa fa-calendar me-2 text-primary"></i>Pick a Date &amp; Time Slot</h5>
    <?php if ($sel_doctor): ?>
    <div class="alert alert-light border mb-3 py-2">
        <strong><?php echo e($sel_doctor['name']); ?></strong>
        <?php if ($sel_doctor['specialty']): ?> &mdash; <?php echo e($sel_doctor['specialty']); ?><?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label fw-semibold">Select Date</label>
        <input type="date" class="form-control" id="date_picker"
               min="<?php echo date('Y-m-d'); ?>"
               max="<?php echo date('Y-m-d', strtotime('+90 days')); ?>"
               value="<?php echo e($appt_date ?: date('Y-m-d')); ?>"
               onchange="goDate(this.value)" style="max-width:240px;">
    </div>
    <?php if ($appt_date): ?>
    <h6><?php echo fmt_date($appt_date,'l, d F Y'); ?></h6>
    <?php if (empty($slots)): ?>
    <div class="alert alert-warning">
        <i class="fa fa-calendar-xmark me-2"></i>No available slots on this date. Please try another date.
    </div>
    <?php else: ?>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($slots as $slot): ?>
        <a href="booking.php?t=<?php echo e($slug); ?>&clinic=<?php echo e($clinic_id); ?>&doctor=<?php echo e($doctor_id); ?>&date=<?php echo urlencode($appt_date); ?>&time=<?php echo urlencode($slot); ?>"
           class="btn btn-outline-primary slot-btn">
            <?php echo e($slot); ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <p class="text-muted">Please select a date above to see available time slots.</p>
    <?php endif; ?>

    <!-- STEP 4: Patient details -->
    <?php else: ?>
    <div class="mb-3">
        <a href="booking.php?t=<?php echo e($slug); ?>&clinic=<?php echo e($clinic_id); ?>&doctor=<?php echo e($doctor_id); ?>&date=<?php echo urlencode($appt_date); ?>" class="text-decoration-none small">
            <i class="fa fa-arrow-left me-1"></i>Change time slot
        </a>
    </div>
    <h5 class="mb-3"><i class="fa fa-clipboard me-2 text-primary"></i>Your Details</h5>

    <!-- Booking summary -->
    <div class="card bg-light border mb-4">
        <div class="card-body py-2">
            <div class="row small">
                <?php if ($sel_clinic): ?><div class="col-auto"><strong>Clinic:</strong> <?php echo e($sel_clinic['name']); ?></div><?php endif; ?>
                <?php if ($sel_doctor): ?><div class="col-auto"><strong>Doctor:</strong> <?php echo e($sel_doctor['name']); ?></div><?php endif; ?>
                <div class="col-auto"><strong>Date:</strong> <?php echo fmt_date($appt_date,'d F Y'); ?></div>
                <div class="col-auto"><strong>Time:</strong> <?php echo e($appt_time); ?></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="clinic_id" value="<?php echo e($clinic_id); ?>">
                <input type="hidden" name="doctor_id" value="<?php echo e($doctor_id); ?>">
                <input type="hidden" name="appt_date" value="<?php echo e($appt_date); ?>">
                <input type="hidden" name="appt_time" value="<?php echo e($appt_time); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="patient_name" required maxlength="255"
                               value="<?php echo e($_POST['patient_name']??''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="patient_phone" required maxlength="50"
                               value="<?php echo e($_POST['patient_phone']??''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" name="patient_email" maxlength="255"
                               value="<?php echo e($_POST['patient_email']??''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date of Birth</label>
                        <input type="date" class="form-control" name="patient_dob"
                               value="<?php echo e($_POST['patient_dob']??''); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes / Reason for Visit</label>
                        <textarea class="form-control" name="patient_notes" rows="3" maxlength="1000"><?php echo e($_POST['patient_notes']??''); ?></textarea>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa fa-check me-2"></i>Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

</div><!-- /.container -->

<footer class="py-3 text-center text-muted small border-top mt-4">
    <?php echo e($tenant['name'] ?? $app_name); ?> &mdash; Powered by <?php echo e($app_name); ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function goDate(val) {
    var url = 'booking.php?t=<?php echo e($slug); ?>'
            + '&clinic=<?php echo e($clinic_id); ?>'
            + '&doctor=<?php echo e($doctor_id); ?>'
            + '&date=' + encodeURIComponent(val);
    window.location = url;
}
</script>
</body>
</html>
