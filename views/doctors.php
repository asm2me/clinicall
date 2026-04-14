<?php
Auth::requireLogin();
Auth::requirePermission('doctor.view');
$page_title = 'Doctors';
$tid = Auth::tenantId();
if (!$tid) { flash('Select a tenant first.','warning'); redirect('?page=dashboard'); }

// Bulk delete
if (is_post() && csrf_verify() && post('_action') === 'bulk_delete') {
    Auth::requirePermission('doctor.delete');
    $ids = array_filter(array_map('clean_uuid', (array)($_POST['ids'] ?? [])));
    foreach ($ids as $id) {
        Database::exec("DELETE FROM doctors WHERE id=:id AND tenant_id=:t", [':id'=>$id,':t'=>$tid]);
    }
    flash(count($ids) . ' doctor(s) deleted.');
    redirect('?page=doctors');
}

$filter_clinic = clean_uuid(get_param('clinic_id'));
$search        = trim(get_param('q'));

$params = [':t' => $tid];
$where  = 'd.tenant_id = :t';
if ($filter_clinic) { $where .= ' AND d.clinic_id = :c'; $params[':c'] = $filter_clinic; }
if ($search) {
    $op     = Database::likeOp();
    $where .= " AND (d.name $op :q OR d.specialty $op :q OR d.email $op :q)";
    $params[':q'] = "%$search%";
}

$doctors = Database::all(
    "SELECT d.*, c.name AS clinic_name,
        (SELECT COUNT(*) FROM doctor_schedules s WHERE s.doctor_id=d.id AND s.enabled=".Database::bool(true).") AS schedule_count,
        (SELECT COUNT(*) FROM appointments a WHERE a.doctor_id=d.id AND a.appointment_date >= CURRENT_DATE) AS upcoming
     FROM doctors d
     JOIN clinics c ON c.id = d.clinic_id
     WHERE $where ORDER BY c.name, d.name",
    $params
);

$all_clinics = Database::all(
    "SELECT id, name FROM clinics WHERE tenant_id=:t AND enabled=".Database::bool(true)." ORDER BY name",
    [':t'=>$tid]
);

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-user-md me-2 text-primary"></i>Doctors</h4>
    <?php if (Auth::can('doctor.create')): ?>
    <a href="?page=doctor_edit<?php echo $filter_clinic ? "&clinic_id=$filter_clinic" : ''; ?>" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>Add Doctor
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<form method="get" class="mb-3">
    <input type="hidden" name="page" value="doctors">
    <div class="row g-2">
        <div class="col-auto">
            <select class="form-select" name="clinic_id" onchange="this.form.submit()">
                <option value="">All Clinics</option>
                <?php foreach ($all_clinics as $c): ?>
                <option value="<?php echo e($c['id']); ?>" <?php echo ($filter_clinic===$c['id'])?'selected':''; ?>>
                    <?php echo e($c['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <div class="input-group" style="max-width:350px;">
                <input type="text" class="form-control" name="q" placeholder="Search doctors…" value="<?php echo e($search); ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
                <?php if ($search||$filter_clinic): ?><a href="?page=doctors" class="btn btn-outline-danger"><i class="fa fa-times"></i></a><?php endif; ?>
            </div>
        </div>
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
                        <th>Specialty</th>
                        <th>Phone</th>
                        <th>Schedules</th>
                        <th>Upcoming</th>
                        <th>Status</th>
                        <th width="110"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($doctors)): ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <i class="fa fa-user-md fa-2x mb-2"></i><br>No doctors found.
                    </td></tr>
                    <?php else: foreach ($doctors as $d): ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?php echo e($d['id']); ?>" class="form-check-input row-chk"></td>
                        <td>
                            <a href="?page=doctor_edit&id=<?php echo e($d['id']); ?>" class="fw-semibold text-decoration-none">
                                <?php echo e($d['name']); ?>
                            </a>
                            <?php if ($d['email']): ?>
                            <br><small class="text-muted"><?php echo e($d['email']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($d['clinic_name']); ?></td>
                        <td><?php echo e($d['specialty']); ?></td>
                        <td><?php echo e($d['phone']); ?></td>
                        <td>
                            <a href="?page=schedules&doctor_id=<?php echo e($d['id']); ?>" class="badge bg-secondary text-decoration-none">
                                <?php echo $d['schedule_count']; ?> slot(s)
                            </a>
                        </td>
                        <td><?php echo $d['upcoming']; ?></td>
                        <td><?php echo $d['enabled'] ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Disabled</span>'; ?></td>
                        <td>
                            <?php if (Auth::can('doctor.edit')): ?>
                            <a href="?page=doctor_edit&id=<?php echo e($d['id']); ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fa fa-edit"></i></a>
                            <?php endif; ?>
                            <a href="?page=schedules&doctor_id=<?php echo e($d['id']); ?>" class="btn btn-sm btn-outline-info me-1" title="Schedules"><i class="fa fa-clock"></i></a>
                            <?php if (Auth::can('doctor.delete')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteOne('<?php echo e($d['id']); ?>')"><i class="fa fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($doctors) && Auth::can('doctor.delete')): ?>
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
document.getElementById('chk_all').addEventListener('change',function(){
    document.querySelectorAll('.row-chk').forEach(c=>c.checked=this.checked);
});
function bulkDelete(){
    var c=document.querySelectorAll('.row-chk:checked');
    if(!c.length){alert('Select at least one doctor.');return;}
    if(confirm('Delete '+c.length+' doctor(s)?')){document.getElementById('bulk_form').submit();}
}
function confirmDeleteOne(id){
    if(confirm('Delete this doctor?')){
        document.querySelectorAll('.row-chk').forEach(c=>c.checked=false);
        var chk=document.querySelector('.row-chk[value="'+id+'"]');
        if(chk)chk.checked=true;
        document.getElementById('bulk_form').submit();
    }
}
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
