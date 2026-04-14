<?php
Auth::requireLogin();
$tid = Auth::tenantId();
if (!$tid) { flash('Select a tenant first.','warning'); redirect('?page=dashboard'); }

$id      = clean_uuid(get_param('id'));
$is_edit = (bool)$id;
Auth::requirePermission($is_edit ? 'schedule.edit' : 'schedule.create');
$page_title = $is_edit ? 'Edit Schedule Block' : 'Add Schedule Block';

$sched = [];
if ($is_edit) {
    $sched = Database::row("SELECT * FROM doctor_schedules WHERE id=:id AND tenant_id=:t",[':id'=>$id,':t'=>$tid]);
    if (!$sched) { flash('Schedule not found.','danger'); redirect('?page=schedules'); }
}

$all_doctors = Database::all(
    "SELECT d.id, d.name, c.name AS clinic_name FROM doctors d JOIN clinics c ON c.id=d.clinic_id WHERE d.tenant_id=:t AND d.enabled=".Database::bool(true)." ORDER BY c.name,d.name",
    [':t'=>$tid]
);
$preselect_doctor = clean_uuid(get_param('doctor_id'));
$days = days_of_week();

$errors = [];
if (is_post() && csrf_verify()) {
    $doctor_id   = clean_uuid(post('doctor_id'));
    $dow         = (int)post('day_of_week');
    $start       = trim(post('start_time'));
    $end         = trim(post('end_time'));
    $duration    = max(5,(int)post('slot_duration'));
    $max_appts   = max(1,(int)post('max_appointments'));
    $enabled     = (bool)post('enabled');

    if (!$doctor_id)       { $errors[] = 'Doctor is required.'; }
    if (!$start)           { $errors[] = 'Start time is required.'; }
    if (!$end)             { $errors[] = 'End time is required.'; }
    if ($start && $end && $start >= $end) { $errors[] = 'End time must be after start time.'; }
    if ($dow < 0 || $dow > 6) { $errors[] = 'Invalid day.'; }
    if ($doctor_id && !Database::row("SELECT id FROM doctors WHERE id=:id AND tenant_id=:t",[':id'=>$doctor_id,':t'=>$tid])) {
        $errors[] = 'Invalid doctor.';
    }

    if (empty($errors)) {
        $data = [
            'doctor_id'        => $doctor_id,
            'day_of_week'      => $dow,
            'start_time'       => $start,
            'end_time'         => $end,
            'slot_duration'    => $duration,
            'max_appointments' => $max_appts,
            'enabled'          => $enabled,
        ];
        if ($is_edit) {
            Database::update('doctor_schedules', $data, ['id'=>$id,'tenant_id'=>$tid]);
            flash('Schedule updated.');
        } else {
            Database::insert('doctor_schedules', array_merge($data, [
                'id'        => generate_uuid(),
                'tenant_id' => $tid,
            ]));
            flash('Schedule added.');
        }
        redirect('?page=schedules' . ($doctor_id ? "&doctor_id=$doctor_id" : ''));
    }
    $sched = compact('doctor_id','day_of_week','start_time','end_time','slot_duration','max_appointments','enabled');
    $sched['day_of_week'] = $dow;
    $sched['start_time']  = $start;
    $sched['end_time']    = $end;
    $sched['slot_duration']    = $duration;
    $sched['max_appointments'] = $max_appts;
}

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-clock me-2 text-primary"></i><?php echo $page_title; ?></h4>
    <a href="?page=schedules" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo e($e); ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="post">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Doctor <span class="text-danger">*</span></label>
                        <select class="form-select" name="doctor_id" required>
                            <option value="">— Select Doctor —</option>
                            <?php $sel = $sched['doctor_id'] ?? $preselect_doctor;
                            foreach ($all_doctors as $d): ?>
                            <option value="<?php echo e($d['id']); ?>" <?php echo ($sel===$d['id'])?'selected':''; ?>>
                                <?php echo e($d['clinic_name']); ?> › <?php echo e($d['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Day of Week <span class="text-danger">*</span></label>
                        <select class="form-select" name="day_of_week">
                            <?php foreach ($days as $num => $label): ?>
                            <option value="<?php echo $num; ?>" <?php echo ((int)($sched['day_of_week'] ?? 1)===$num)?'selected':''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" required
                                   value="<?php echo substr($sched['start_time'] ?? '08:00',0,5); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" required
                                   value="<?php echo substr($sched['end_time'] ?? '17:00',0,5); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Slot Duration</label>
                            <select class="form-select" name="slot_duration" id="slot_duration" onchange="calcSlots()">
                                <?php foreach ([5,10,15,20,30,45,60] as $m): ?>
                                <option value="<?php echo $m; ?>" <?php echo ((int)($sched['slot_duration']??15)===$m)?'selected':''; ?>><?php echo $m; ?> minutes</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Max Patients per Slot</label>
                            <input type="number" class="form-control" name="max_appointments" min="1" max="50"
                                   value="<?php echo (int)($sched['max_appointments']??1); ?>">
                            <div class="form-text">Allow parallel bookings in the same time slot.</div>
                        </div>
                    </div>
                    <div class="alert alert-info py-2" id="slots_preview">
                        Select times to see slot preview.
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled"
                                <?php echo (!$is_edit || !empty($sched['enabled'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-semibold" for="enabled">Active</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
                        <a href="?page=schedules" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="fa fa-info-circle me-2"></i>How Schedules Work</h6></div>
            <div class="card-body">
                <ul class="small text-muted mb-0">
                    <li>Each block defines a <strong>recurring weekly window</strong> when the doctor is available.</li>
                    <li>You can add <strong>multiple blocks</strong> for the same day (e.g. morning + afternoon).</li>
                    <li>The system auto-generates appointment slots based on start time, end time, and slot duration.</li>
                    <li>Use <strong>Max Patients per Slot</strong> to allow group appointments or parallel consultations.</li>
                    <li>Use <strong>Exceptions</strong> to block specific dates (holidays, sick days).</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$page_scripts = <<<JS
function calcSlots() {
    var start = document.querySelector('input[name=start_time]').value;
    var end   = document.querySelector('input[name=end_time]').value;
    var dur   = parseInt(document.getElementById('slot_duration').value, 10);
    if (!start || !end || start >= end || isNaN(dur)) {
        document.getElementById('slots_preview').textContent = 'Select times to see slot preview.';
        return;
    }
    var slots = [];
    var cur = start.split(':').reduce((a,b)=>a*60+parseInt(b),0);
    var fin = end.split(':').reduce((a,b)=>a*60+parseInt(b),0);
    while (cur < fin) {
        var h = Math.floor(cur/60).toString().padStart(2,'0');
        var m = (cur%60).toString().padStart(2,'0');
        slots.push(h+':'+m);
        cur += dur;
    }
    document.getElementById('slots_preview').innerHTML =
        '<strong>' + slots.length + ' slots:</strong> ' + slots.join(', ');
}
document.querySelector('input[name=start_time]').addEventListener('change', calcSlots);
document.querySelector('input[name=end_time]').addEventListener('change', calcSlots);
calcSlots();
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
