<?php
Auth::requireLogin();
Auth::requirePermission('appointment.view');
$page_title = 'Appointments';
$tid   = Auth::tenantId();
if (!$tid) { flash('Select a tenant first.','warning'); redirect('?page=dashboard'); }

$statuses = appointment_statuses();

// Handle bulk action
if (is_post() && csrf_verify()) {
    $action = post('_action');
    $ids    = array_filter(array_map('clean_uuid', (array)($_POST['ids'] ?? [])));
    if (!$ids) { flash('No items selected.','warning'); redirect('?page=appointments'); }

    if ($action === 'delete') {
        Auth::requirePermission('appointment.delete');
        foreach ($ids as $id) {
            Database::exec("DELETE FROM appointments WHERE id=:id AND tenant_id=:t",[':id'=>$id,':t'=>$tid]);
        }
        flash(count($ids) . ' appointment(s) deleted.');
    } elseif (array_key_exists($action, $statuses)) {
        Auth::requirePermission('appointment.edit');
        foreach ($ids as $id) {
            Database::exec("UPDATE appointments SET status=:s, updated_at=NOW() WHERE id=:id AND tenant_id=:t",
                [':s'=>$action,':id'=>$id,':t'=>$tid]);
        }
        flash('Status changed to "' . $statuses[$action] . '" for ' . count($ids) . ' appointment(s).');
    }
    redirect('?page=appointments');
}

// Filters
$filter_clinic = clean_uuid(get_param('clinic_id'));
$filter_doctor = clean_uuid(get_param('doctor_id'));
$filter_status = preg_replace('/[^a-z_]/','',(string)get_param('status'));
$filter_date   = preg_replace('/[^0-9\-]/','',(string)get_param('date'));
$search        = trim(get_param('q'));

$params = [':t'=>$tid];
$where  = 'a.tenant_id = :t';
if ($filter_clinic) { $where .= ' AND a.clinic_id=:c';   $params[':c']=$filter_clinic; }
if ($filter_doctor) { $where .= ' AND a.doctor_id=:d';   $params[':d']=$filter_doctor; }
if ($filter_status) { $where .= ' AND a.status=:s';      $params[':s']=$filter_status; }
if ($filter_date)   { $where .= ' AND a.appointment_date=:dt'; $params[':dt']=$filter_date; }
if ($search) {
    $op     = Database::likeOp();
    $where .= " AND (a.patient_name $op :q OR a.patient_phone $op :q OR a.patient_email $op :q)";
    $params[':q'] = "%$search%";
}

$appointments = Database::all(
    "SELECT a.*, c.name AS clinic_name, d.name AS doctor_name, d.specialty
     FROM appointments a
     JOIN clinics c ON c.id=a.clinic_id
     JOIN doctors d ON d.id=a.doctor_id
     WHERE $where ORDER BY a.appointment_date DESC, a.appointment_time DESC",
    $params
);

// Filter dropdowns
$all_clinics = Database::all("SELECT id,name FROM clinics WHERE tenant_id=:t ORDER BY name",[':t'=>$tid]);
$all_doctors = Database::all("SELECT d.id,d.name,c.name AS clinic_name FROM doctors d JOIN clinics c ON c.id=d.clinic_id WHERE d.tenant_id=:t ORDER BY c.name,d.name",[':t'=>$tid]);

// Today summary
$today = date('Y-m-d');
$summary_rows = Database::all(
    "SELECT status, COUNT(*) AS cnt FROM appointments WHERE tenant_id=:t AND appointment_date=:d GROUP BY status",
    [':t'=>$tid,':d'=>$today]
);
$summary = [];
foreach ($summary_rows as $r) { $summary[$r['status']] = (int)$r['cnt']; }

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-calendar-check me-2 text-primary"></i>Appointments</h4>
    <?php if (Auth::can('appointment.create')): ?>
    <a href="?page=appointment_edit" class="btn btn-primary"><i class="fa fa-plus me-1"></i>New Appointment</a>
    <?php endif; ?>
</div>

<!-- Today summary -->
<?php if (!empty($summary) || !$filter_date): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted small me-2">Today (<?php echo date('d M Y'); ?>):</span>
        <?php foreach ($statuses as $k => $v): ?>
        <a href="?page=appointments&date=<?php echo $today; ?>&status=<?php echo $k; ?>"
           class="text-decoration-none">
            <?php echo status_badge($k); ?>
            <span class="ms-1 small"><?php echo $summary[$k] ?? 0; ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<form method="get" class="mb-3" id="filter_form">
    <input type="hidden" name="page" value="appointments">
    <div class="row g-2">
        <div class="col-auto">
            <select class="form-select form-select-sm" name="clinic_id" onchange="this.form.submit()">
                <option value="">All Clinics</option>
                <?php foreach ($all_clinics as $c): ?>
                <option value="<?php echo e($c['id']); ?>" <?php echo ($filter_clinic===$c['id'])?'selected':''; ?>><?php echo e($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select form-select-sm" name="doctor_id" onchange="this.form.submit()">
                <option value="">All Doctors</option>
                <?php foreach ($all_doctors as $d): ?>
                <option value="<?php echo e($d['id']); ?>" <?php echo ($filter_doctor===$d['id'])?'selected':''; ?>><?php echo e($d['clinic_name']); ?> / <?php echo e($d['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $k=>$v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($filter_status===$k)?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <input type="date" class="form-control form-control-sm" name="date" value="<?php echo e($filter_date); ?>" onchange="this.form.submit()">
        </div>
        <div class="col">
            <div class="input-group input-group-sm" style="max-width:280px;">
                <input type="text" class="form-control" name="q" placeholder="Search patient…" value="<?php echo e($search); ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
            </div>
        </div>
        <?php if ($filter_clinic||$filter_doctor||$filter_status||$filter_date||$search): ?>
        <div class="col-auto"><a href="?page=appointments" class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i> Clear</a></div>
        <?php endif; ?>
    </div>
</form>

<!-- Bulk actions -->
<?php if (Auth::can('appointment.edit') || Auth::can('appointment.delete')): ?>
<div class="mb-2 d-flex flex-wrap gap-1 align-items-center">
    <span class="text-muted small me-2">Selected:</span>
    <?php if (Auth::can('appointment.edit')): ?>
    <?php foreach ($statuses as $k => $v): ?>
    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="bulkAction('<?php echo $k; ?>')">
        → <?php echo $v; ?>
    </button>
    <?php endforeach; ?>
    <?php endif; ?>
    <?php if (Auth::can('appointment.delete')): ?>
    <button type="button" class="btn btn-xs btn-outline-danger ms-2" onclick="bulkAction('delete')">
        <i class="fa fa-trash"></i> Delete
    </button>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <form method="post" id="bulk_form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_action" id="bulk_action_val" value="">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" id="chk_all" class="form-check-input"></th>
                        <th>Patient</th>
                        <th>Phone</th>
                        <th>Clinic</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Dur.</th>
                        <th>Status</th>
                        <th width="60"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($appointments)): ?>
                    <tr><td colspan="10" class="text-center py-5 text-muted">
                        <i class="fa fa-calendar fa-2x mb-2"></i><br>No appointments found.
                    </td></tr>
                    <?php else: foreach ($appointments as $a): ?>
                    <tr class="<?php echo ($a['appointment_date']===$today)?'table-active':''; ?>">
                        <td><input type="checkbox" name="ids[]" value="<?php echo e($a['id']); ?>" class="form-check-input row-chk"></td>
                        <td>
                            <?php if (Auth::can('appointment.edit')): ?>
                            <a href="?page=appointment_edit&id=<?php echo e($a['id']); ?>" class="fw-semibold text-decoration-none"><?php echo e($a['patient_name']); ?></a>
                            <?php else: ?>
                            <span class="fw-semibold"><?php echo e($a['patient_name']); ?></span>
                            <?php endif; ?>
                            <?php if ($a['patient_email']): ?>
                            <br><small class="text-muted"><?php echo e($a['patient_email']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($a['patient_phone']); ?></td>
                        <td><?php echo e($a['clinic_name']); ?></td>
                        <td>
                            <?php echo e($a['doctor_name']); ?>
                            <?php if ($a['specialty']): ?><br><small class="text-muted"><?php echo e($a['specialty']); ?></small><?php endif; ?>
                        </td>
                        <td><?php echo fmt_date($a['appointment_date'],'d M Y'); ?></td>
                        <td class="fw-semibold"><?php echo substr($a['appointment_time'],0,5); ?></td>
                        <td><?php echo (int)$a['duration']; ?>m</td>
                        <td><?php echo status_badge($a['status']); ?></td>
                        <td>
                            <?php if (Auth::can('appointment.edit')): ?>
                            <a href="?page=appointment_edit&id=<?php echo e($a['id']); ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-light border-top small text-muted">
                <?php echo count($appointments); ?> appointment(s) found.
            </div>
        </form>
    </div>
</div>

<?php
$page_scripts = <<<JS
document.getElementById('chk_all').addEventListener('change',function(){document.querySelectorAll('.row-chk').forEach(c=>c.checked=this.checked);});
function bulkAction(action){
    var checked=document.querySelectorAll('.row-chk:checked');
    if(!checked.length){alert('Please select at least one appointment.');return;}
    var msg=action==='delete'?'Delete '+checked.length+' appointment(s)?':'Change status of '+checked.length+' appointment(s)?';
    if(confirm(msg)){
        document.getElementById('bulk_action_val').value=action;
        document.getElementById('bulk_form').submit();
    }
}
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
