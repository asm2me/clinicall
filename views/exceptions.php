<?php
Auth::requireLogin();
Auth::requirePermission('exception.view');
$page_title = 'Schedule Exceptions';
$tid = Auth::tenantId();
if (!$tid) { flash('Select a tenant first.','warning'); redirect('?page=dashboard'); }

$filter_doctor = clean_uuid(get_param('doctor_id'));

// Add
if (is_post() && csrf_verify() && post('_action') === 'add') {
    Auth::requirePermission('exception.create');
    $doc_id   = clean_uuid(post('doctor_id'));
    $exc_date = preg_replace('/[^0-9\-]/','',(string)post('exception_date'));
    $exc_type = in_array(post('exception_type'),['off','custom']) ? post('exception_type') : 'off';
    $note     = trim(post('note'));
    $start    = $exc_type==='custom' ? trim(post('start_time')) : null;
    $end      = $exc_type==='custom' ? trim(post('end_time'))   : null;

    if ($doc_id && $exc_date &&
        Database::row("SELECT id FROM doctors WHERE id=:id AND tenant_id=:t",[':id'=>$doc_id,':t'=>$tid])) {
        Database::insert('schedule_exceptions',[
            'id'             => generate_uuid(),
            'tenant_id'      => $tid,
            'doctor_id'      => $doc_id,
            'exception_date' => $exc_date,
            'exception_type' => $exc_type,
            'start_time'     => $start,
            'end_time'       => $end,
            'note'           => $note ?: null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        flash('Exception added.');
    } else {
        flash('Invalid doctor or date.','danger');
    }
    redirect('?page=exceptions' . ($filter_doctor ? "&doctor_id=$filter_doctor" : ''));
}

// Delete
if (is_post() && csrf_verify() && post('_action') === 'bulk_delete') {
    Auth::requirePermission('exception.delete');
    $ids = array_filter(array_map('clean_uuid', (array)($_POST['ids'] ?? [])));
    foreach ($ids as $id) {
        Database::exec("DELETE FROM schedule_exceptions WHERE id=:id AND tenant_id=:t",[':id'=>$id,':t'=>$tid]);
    }
    flash(count($ids) . ' exception(s) removed.');
    redirect('?page=exceptions' . ($filter_doctor ? "&doctor_id=$filter_doctor" : ''));
}

// Load data
$params = [':t'=>$tid];
$where  = 'e.tenant_id = :t';
if ($filter_doctor) { $where .= ' AND e.doctor_id = :d'; $params[':d'] = $filter_doctor; }

$exceptions = Database::all(
    "SELECT e.*, d.name AS doctor_name, c.name AS clinic_name
     FROM schedule_exceptions e
     JOIN doctors d ON d.id = e.doctor_id
     JOIN clinics c ON c.id = d.clinic_id
     WHERE $where ORDER BY e.exception_date DESC, d.name",
    $params
);

$all_doctors = Database::all(
    "SELECT d.id, d.name, c.name AS clinic_name FROM doctors d JOIN clinics c ON c.id=d.clinic_id WHERE d.tenant_id=:t ORDER BY c.name,d.name",
    [':t'=>$tid]
);

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-calendar-xmark me-2 text-primary"></i>Schedule Exceptions</h4>
</div>

<!-- Add form -->
<?php if (Auth::can('exception.create')): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Add Exception (Holiday / Day Off)</div>
    <div class="card-body">
        <form method="post" class="row g-3 align-items-end">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_action" value="add">
            <div class="col-md-3">
                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                <select class="form-select" name="doctor_id" required>
                    <option value="">— Select —</option>
                    <?php foreach ($all_doctors as $d): ?>
                    <option value="<?php echo e($d['id']); ?>" <?php echo ($filter_doctor===$d['id'])?'selected':''; ?>>
                        <?php echo e($d['clinic_name']); ?> / <?php echo e($d['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="exception_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select class="form-select" name="exception_type" onchange="toggleCustom(this.value)">
                    <option value="off">Full Day Off</option>
                    <option value="custom">Custom Hours</option>
                </select>
            </div>
            <div class="col-md-2" id="custom_hours" style="display:none;">
                <label class="form-label">From &rarr; To</label>
                <div class="d-flex gap-1">
                    <input type="time" class="form-control form-control-sm" name="start_time">
                    <input type="time" class="form-control form-control-sm" name="end_time">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Note</label>
                <input type="text" class="form-control" name="note" maxlength="200" placeholder="e.g. Public Holiday">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="fa fa-plus me-1"></i>Add</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Filter -->
<div class="mb-3">
    <form method="get" style="display:inline;">
        <input type="hidden" name="page" value="exceptions">
        <select class="form-select d-inline-block w-auto" name="doctor_id" onchange="this.form.submit()">
            <option value="">All Doctors</option>
            <?php foreach ($all_doctors as $d): ?>
            <option value="<?php echo e($d['id']); ?>" <?php echo ($filter_doctor===$d['id'])?'selected':''; ?>>
                <?php echo e($d['clinic_name']); ?> / <?php echo e($d['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php if ($filter_doctor): ?>
        <a href="?page=exceptions" class="btn btn-outline-danger ms-1"><i class="fa fa-times"></i></a>
        <?php endif; ?>
    </form>
</div>

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
                        <th>Date</th>
                        <th>Day</th>
                        <th>Type</th>
                        <th>Hours</th>
                        <th>Note</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($exceptions)): ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted">No exceptions defined.</td></tr>
                    <?php else: foreach ($exceptions as $ex): ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?php echo e($ex['id']); ?>" class="form-check-input row-chk"></td>
                        <td class="fw-semibold"><?php echo e($ex['doctor_name']); ?></td>
                        <td><?php echo e($ex['clinic_name']); ?></td>
                        <td><?php echo fmt_date($ex['exception_date'],'d M Y'); ?></td>
                        <td><?php echo days_of_week()[(int)date('w',strtotime($ex['exception_date']))]; ?></td>
                        <td>
                            <?php if ($ex['exception_type']==='custom'): ?>
                            <span class="badge text-bg-info">Custom Hours</span>
                            <?php else: ?>
                            <span class="badge text-bg-warning text-dark">Full Day Off</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ex['start_time']): ?>
                            <?php echo substr($ex['start_time'],0,5); ?> – <?php echo substr($ex['end_time'],0,5); ?>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($ex['note'] ?? ''); ?></td>
                        <td>
                            <?php if (Auth::can('exception.delete')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteOne('<?php echo e($ex['id']); ?>')"><i class="fa fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($exceptions) && Auth::can('exception.delete')): ?>
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
function toggleCustom(v) { document.getElementById('custom_hours').style.display = v==='custom' ? '' : 'none'; }
document.getElementById('chk_all').addEventListener('change',function(){document.querySelectorAll('.row-chk').forEach(c=>c.checked=this.checked);});
function bulkDelete(){var c=document.querySelectorAll('.row-chk:checked');if(!c.length){alert('Select at least one.');return;}if(confirm('Remove '+c.length+' exception(s)?')){document.getElementById('bulk_form').submit();}}
function confirmDeleteOne(id){if(confirm('Remove this exception?')){document.querySelectorAll('.row-chk').forEach(c=>c.checked=false);var chk=document.querySelector('.row-chk[value="'+id+'"]');if(chk)chk.checked=true;document.getElementById('bulk_form').submit();}}
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
