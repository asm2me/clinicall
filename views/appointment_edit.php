<?php
Auth::requireLogin();
$tid = Auth::tenantId();
if (!$tid) { flash('Select a tenant first.','warning'); redirect('?page=dashboard'); }

$id      = clean_uuid(get_param('id'));
$is_edit = (bool)$id;
Auth::requirePermission($is_edit ? 'appointment.edit' : 'appointment.create');
$page_title = $is_edit ? 'Edit Appointment' : 'New Appointment';

$appt = [];
if ($is_edit) {
    $appt = Database::row("SELECT * FROM appointments WHERE id=:id AND tenant_id=:t",[':id'=>$id,':t'=>$tid]);
    if (!$appt) { flash('Appointment not found.','danger'); redirect('?page=appointments'); }
}

$statuses  = appointment_statuses();
$appt_types= ['general','follow-up','consultation','emergency','routine-check','surgery-pre','surgery-post','lab-review','vaccination','other'];
$all_clinics = Database::all("SELECT id,name FROM clinics WHERE tenant_id=:t AND enabled=".Database::bool(true)." ORDER BY name",[':t'=>$tid]);
$all_doctors = Database::all("SELECT d.id,d.name,d.specialty,d.clinic_id,c.name AS clinic_name FROM doctors d JOIN clinics c ON c.id=d.clinic_id WHERE d.tenant_id=:t AND d.enabled=".Database::bool(true)." ORDER BY c.name,d.name",[':t'=>$tid]);

$errors = [];
if (is_post() && csrf_verify()) {
    $clinic_id = clean_uuid(post('clinic_id'));
    $doctor_id = clean_uuid(post('doctor_id'));
    $pat_name  = trim(post('patient_name'));
    $pat_phone = trim(post('patient_phone'));
    $pat_email = trim(post('patient_email'));
    $pat_dob   = trim(post('patient_dob'));
    $pat_notes = trim(post('patient_notes'));
    $appt_date = preg_replace('/[^0-9\-]/','',post('appointment_date'));
    $appt_time = preg_replace('/[^0-9\:]/','',(string)post('appointment_time'));
    $duration  = max(5,(int)post('duration'));
    $status    = array_key_exists(post('status'),$statuses) ? post('status') : 'pending';
    $type      = in_array(post('type'),$appt_types) ? post('type') : 'general';
    $staff_notes = trim(post('staff_notes'));

    if (!$clinic_id)   { $errors[] = 'Clinic is required.'; }
    if (!$doctor_id)   { $errors[] = 'Doctor is required.'; }
    if (!$pat_name)    { $errors[] = 'Patient name is required.'; }
    if (!$pat_phone)   { $errors[] = 'Patient phone is required.'; }
    if (!$appt_date)   { $errors[] = 'Appointment date is required.'; }
    if (!$appt_time)   { $errors[] = 'Time slot is required.'; }

    if (empty($errors)) {
        $data = [
            'clinic_id'        => $clinic_id,
            'doctor_id'        => $doctor_id,
            'patient_name'     => $pat_name,
            'patient_phone'    => $pat_phone,
            'patient_email'    => $pat_email ?: null,
            'patient_dob'      => $pat_dob   ?: null,
            'patient_notes'    => $pat_notes ?: null,
            'appointment_date' => $appt_date,
            'appointment_time' => $appt_time,
            'duration'         => $duration,
            'status'           => $status,
            'type'             => $type,
            'staff_notes'      => $staff_notes ?: null,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];
        if ($is_edit) {
            Database::update('appointments', $data, ['id'=>$id,'tenant_id'=>$tid]);
            flash('Appointment updated.');
        } else {
            Database::insert('appointments', array_merge($data, [
                'id'        => generate_uuid(),
                'tenant_id' => $tid,
                'created_at'=> date('Y-m-d H:i:s'),
            ]));
            flash('Appointment booked.');
        }
        redirect('?page=appointments');
    }
    $appt = compact('clinic_id','doctor_id','patient_name','patient_phone','patient_email','patient_dob','patient_notes','appointment_date','appointment_time','duration','status','type','staff_notes');
    $appt['appointment_date'] = $appt_date;
    $appt['appointment_time'] = $appt_time;
}

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-calendar-plus me-2 text-primary"></i><?php echo $page_title; ?></h4>
    <a href="?page=appointments" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo e($e); ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<!-- Pass doctors to JS -->
<script>
var DOCTORS = <?php echo json_encode(array_values($all_doctors), JSON_HEX_TAG); ?>;
function filterDoctors(clinicId) {
    var sel = document.getElementById('doctor_id');
    var prev = sel.value;
    sel.innerHTML = '<option value="">— Select Doctor —</option>';
    DOCTORS.forEach(function(d) {
        if (!clinicId || d.clinic_id === clinicId) {
            var o = new Option(d.clinic_name + ' › ' + d.name + (d.specialty?' ('+d.specialty+')':''), d.id);
            if (d.id === prev) o.selected = true;
            sel.appendChild(o);
        }
    });
    loadSlots();
}
function loadSlots() {
    var doc  = document.getElementById('doctor_id').value;
    var date = document.getElementById('appointment_date').value;
    var tSel = document.getElementById('appointment_time');
    var prev = '<?php echo substr($appt['appointment_time']??'',0,5); ?>';
    tSel.innerHTML = '<option value="">Loading…</option>';
    if (!doc || !date) { tSel.innerHTML = '<option value="">Select doctor & date first</option>'; return; }
    fetch('ajax.php?action=slots&doctor_id='+encodeURIComponent(doc)+'&date='+encodeURIComponent(date)+'&tid=<?php echo e($tid); ?>')
        .then(r => r.json())
        .then(function(slots) {
            tSel.innerHTML = '';
            if (!slots.length) { tSel.innerHTML = '<option value="">No slots available</option>'; return; }
            tSel.appendChild(new Option('— Select time —',''));
            slots.forEach(function(s) {
                var o = new Option(s, s);
                if (s === prev) o.selected = true;
                tSel.appendChild(o);
            });
        })
        .catch(function() { tSel.innerHTML = '<option value="">Error loading slots</option>'; });
}
window.addEventListener('DOMContentLoaded', function() {
    filterDoctors(document.getElementById('clinic_id').value);
});
</script>

<form method="post">
    <?php echo csrf_field(); ?>
    <div class="row g-4">
        <!-- Patient info -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="fa fa-user me-2"></i>Patient Information</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="patient_name" required maxlength="255"
                               value="<?php echo e($appt['patient_name']??''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="patient_phone" required maxlength="50"
                               value="<?php echo e($appt['patient_phone']??''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" name="patient_email" maxlength="255"
                               value="<?php echo e($appt['patient_email']??''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date of Birth</label>
                        <input type="date" class="form-control" name="patient_dob"
                               value="<?php echo e($appt['patient_dob']??''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Patient Notes / Reason</label>
                        <textarea class="form-control" name="patient_notes" rows="3"><?php echo e($appt['patient_notes']??''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment details -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="fa fa-calendar me-2"></i>Appointment Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Clinic <span class="text-danger">*</span></label>
                        <select class="form-select" name="clinic_id" id="clinic_id" required onchange="filterDoctors(this.value)">
                            <option value="">— Select Clinic —</option>
                            <?php foreach ($all_clinics as $c): ?>
                            <option value="<?php echo e($c['id']); ?>" <?php echo (($appt['clinic_id']??'')===$c['id'])?'selected':''; ?>><?php echo e($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Doctor <span class="text-danger">*</span></label>
                        <select class="form-select" name="doctor_id" id="doctor_id" required onchange="loadSlots()">
                            <option value="">— Select Clinic first —</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="appointment_date" id="appointment_date" required
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo e($appt['appointment_date']??''); ?>"
                               onchange="loadSlots()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Time Slot <span class="text-danger">*</span></label>
                        <select class="form-select" name="appointment_time" id="appointment_time" required>
                            <option value="">— Select doctor & date first —</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Duration</label>
                            <select class="form-select" name="duration">
                                <?php foreach ([5,10,15,20,30,45,60] as $m): ?>
                                <option value="<?php echo $m; ?>" <?php echo ((int)($appt['duration']??15)===$m)?'selected':''; ?>><?php echo $m; ?> min</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Type</label>
                            <select class="form-select" name="type">
                                <?php foreach ($appt_types as $t): ?>
                                <option value="<?php echo $t; ?>" <?php echo (($appt['type']??'general')===$t)?'selected':''; ?>>
                                    <?php echo ucwords(str_replace('-',' ',$t)); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="status">
                            <?php foreach ($statuses as $k=>$v): ?>
                            <option value="<?php echo $k; ?>" <?php echo (($appt['status']??'pending')===$k)?'selected':''; ?>><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Staff Notes (internal)</label>
                        <textarea class="form-control" name="staff_notes" rows="2"><?php echo e($appt['staff_notes']??''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save Appointment</button>
        <a href="?page=appointments" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php require_once CLINICALL_ROOT . '/views/layout/footer.php'; ?>
