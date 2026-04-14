<?php
Auth::requireLogin();
Auth::requirePermission('schedule.view');
$page_title = 'Doctor Schedules';
$tid = Auth::tenantId();
if (!$tid) { flash('Select a tenant first.','warning'); redirect('?page=dashboard'); }

// Bulk delete
if (is_post() && csrf_verify() && post('_action') === 'bulk_delete') {
    Auth::requirePermission('schedule.delete');
    $ids = array_filter(array_map('clean_uuid', (array)($_POST['ids'] ?? [])));
    foreach ($ids as $id) {
        Database::exec("DELETE FROM doctor_schedules WHERE id=:id AND tenant_id=:t",[':id'=>$id,':t'=>$tid]);
    }
    flash(count($ids) . ' schedule(s) deleted.');
    redirect('?page=schedules');
}

$filter_doctor = clean_uuid(get_param('doctor_id'));
$filter_clinic = clean_uuid(get_param('clinic_id'));
$days = days_of_week();

$params = [':t' => $tid];
$where  = 's.tenant_id = :t';
if ($filter_doctor) { $where .= ' AND s.doctor_id = :d'; $params[':d'] = $filter_doctor; }
if ($filter_clinic) { $where .= ' AND d.clinic_id = :c'; $params[':c'] = $filter_clinic; }

$schedules = Database::all(
    "SELECT s.*, d.name AS doctor_name, d.specialty, c.name AS clinic_name
     FROM doctor_schedules s
     JOIN doctors d ON d.id = s.doctor_id
     JOIN clinics c ON c.id = d.clinic_id
     WHERE $where ORDER BY c.name, d.name, s.day_of_week, s.start_time",
    $params
);

// Dropdowns
$all_clinics = Database::all("SELECT id,name FROM clinics WHERE tenant_id=:t ORDER BY name",[':t'=>$tid]);
$all_doctors = Database::all(
    "SELECT d.id, d.name, c.name AS clinic_name FROM doctors d JOIN clinics c ON c.id=d.clinic_id WHERE d.tenant_id=:t ORDER BY c.name,d.name",
    [':t'=>$tid]
);

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-clock me-2 text-primary"></i>Doctor Schedules</h4>
    <?php if (Auth::can('schedule.create')): ?>
    <a href="?page=schedule_edit<?php echo $filter_doctor ? "&doctor_id=$filter_doctor" : ''; ?>" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>Add Schedule Block
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<form method="get" class="mb-3">
    <input type="hidden" name="page" value="schedules">
    <div class="row g-2">
        <div class="col-auto">
            <select class="form-select" name="clinic_id" onchange="this.form.submit()">
                <option value="">All Clinics</option>
                <?php foreach ($all_clinics as $c): ?>
                <option value="<?php echo e($c['id']); ?>" <?php echo ($filter_clinic===$c['id'])?'selected':''; ?>><?php echo e($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="doctor_id" onchange="this.form.submit()">
                <option value="">All Doctors</option>
                <?php foreach ($all_doctors as $d): ?>
                <option value="<?php echo e($d['id']); ?>" <?php echo ($filter_doctor===$d['id'])?'selected':''; ?>>
                    <?php echo e($d['clinic_name']); ?> / <?php echo e($d['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($filter_doctor || $filter_clinic): ?>
        <div class="col-auto"><a href="?page=schedules" class="btn btn-outline-danger"><i class="fa fa-times"></i> Clear</a></div>
        <?php endif; ?>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <form method="post" id="bulk_form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_action" value="bulk_delete">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" id="chk_all" class="form-check-input"></th>
                        <th>Doctor</th>
                        <th>Clinic</th>
                        <th>Day</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Slot</th>
                        <th>Max/Slot</th>
                        <th>Slots/Day</th>
                        <th>Status</th>
                        <th width="80"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($schedules)): ?>
                    <tr><td colspan="11" class="text-center py-5 text-muted">
                        <i class="fa fa-calendar fa-2x mb-2"></i><br>No schedules found.
                        <?php if (Auth::can('schedule.create')): ?>
                        <a href="?page=schedule_edit">Add one</a>
                        <?php endif; ?>
                    </td></tr>
                    <?php else: foreach ($schedules as $s):
                        $start = substr($s['start_time'],0,5);
                        $end   = substr($s['end_time'],0,5);
                        $dur   = (int)$s['slot_duration'];
                        $slots_count = count(time_slots($start,$end,$dur));
                    ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?php echo e($s['id']); ?>" class="form-check-input row-chk"></td>
                        <td>
                            <a href="?page=doctor_edit&id=<?php echo e($s['doctor_id']); ?>" class="fw-semibold text-decoration-none">
                                <?php echo e($s['doctor_name']); ?>
                            </a>
                            <?php if ($s['specialty']): ?><br><small class="text-muted"><?php echo e($s['specialty']); ?></small><?php endif; ?>
                        </td>
                        <td><?php echo e($s['clinic_name']); ?></td>
                        <td><span class="badge text-bg-light text-dark border"><?php echo $days[(int)$s['day_of_week']]; ?></span></td>
                        <td class="fw-semibold"><?php echo $start; ?></td>
                        <td class="fw-semibold"><?php echo $end; ?></td>
                        <td><?php echo $dur; ?> min</td>
                        <td><?php echo (int)$s['max_appointments']; ?></td>
                        <td><?php echo $slots_count; ?></td>
                        <td><?php echo $s['enabled'] ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Off</span>'; ?></td>
                        <td>
                            <?php if (Auth::can('schedule.edit')): ?>
                            <a href="?page=schedule_edit&id=<?php echo e($s['id']); ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fa fa-edit"></i></a>
                            <?php endif; ?>
                            <?php if (Auth::can('schedule.delete')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteOne('<?php echo e($s['id']); ?>')"><i class="fa fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($schedules) && Auth::can('schedule.delete')): ?>
            <div class="p-3 border-top bg-light">
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()">
                    <i class="fa fa-trash me-1"></i>Delete Selected
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php
$page_scripts = <<<JS
document.getElementById('chk_all').addEventListener('change',function(){document.querySelectorAll('.row-chk').forEach(c=>c.checked=this.checked);});
function bulkDelete(){var c=document.querySelectorAll('.row-chk:checked');if(!c.length){alert('Select at least one.');return;}if(confirm('Delete '+c.length+' schedule(s)?')){document.getElementById('bulk_form').submit();}}
function confirmDeleteOne(id){if(confirm('Delete this schedule?')){document.querySelectorAll('.row-chk').forEach(c=>c.checked=false);var chk=document.querySelector('.row-chk[value="'+id+'"]');if(chk)chk.checked=true;document.getElementById('bulk_form').submit();}}
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
