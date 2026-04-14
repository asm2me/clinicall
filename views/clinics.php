<?php
Auth::requireLogin();
Auth::requirePermission('clinic.view');
$page_title = 'Clinics';
$tid = Auth::tenantId();
if (!$tid) { flash('Please select a tenant first.','warning'); redirect('?page=dashboard'); }

// Bulk delete
if (is_post() && csrf_verify() && post('_action') === 'bulk_delete') {
    Auth::requirePermission('clinic.delete');
    $ids = array_filter(array_map('clean_uuid', (array)($_POST['ids'] ?? [])));
    foreach ($ids as $id) {
        Database::exec("DELETE FROM clinics WHERE id=:id AND tenant_id=:t", [':id'=>$id,':t'=>$tid]);
    }
    flash(count($ids) . ' clinic(s) deleted.');
    redirect('?page=clinics');
}

$search = trim(get_param('q'));
$params = [':t' => $tid];
$where  = 'c.tenant_id = :t';
if ($search) {
    $op     = Database::likeOp();
    $where .= " AND (c.name $op :q OR c.phone $op :q OR c.email $op :q)";
    $params[':q'] = "%$search%";
}

$clinics = Database::all(
    "SELECT c.*,
        (SELECT COUNT(*) FROM doctors d WHERE d.clinic_id=c.id) AS doctor_count,
        (SELECT COUNT(*) FROM appointments a WHERE a.clinic_id=c.id AND a.appointment_date >= CURRENT_DATE) AS upcoming
     FROM clinics c WHERE $where ORDER BY c.name",
    $params
);

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-hospital me-2 text-primary"></i>Clinics</h4>
    <?php if (Auth::can('clinic.create')): ?>
    <a href="?page=clinic_edit" class="btn btn-primary"><i class="fa fa-plus me-1"></i>Add Clinic</a>
    <?php endif; ?>
</div>

<!-- Search -->
<form method="get" class="mb-3">
    <input type="hidden" name="page" value="clinics">
    <div class="input-group" style="max-width:400px;">
        <input type="text" class="form-control" name="q" placeholder="Search clinics…" value="<?php echo e($search); ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
        <?php if ($search): ?><a href="?page=clinics" class="btn btn-outline-danger"><i class="fa fa-times"></i></a><?php endif; ?>
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
                        <th>Clinic Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Doctors</th>
                        <th>Upcoming Appts</th>
                        <th>Timezone</th>
                        <th>Status</th>
                        <th width="100"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($clinics)): ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <i class="fa fa-hospital fa-2x mb-2"></i><br>
                        No clinics found.
                        <?php if (Auth::can('clinic.create')): ?>
                        <a href="?page=clinic_edit">Add the first one</a>
                        <?php endif; ?>
                    </td></tr>
                    <?php else: foreach ($clinics as $c): ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?php echo e($c['id']); ?>" class="form-check-input row-chk"></td>
                        <td>
                            <a href="?page=clinic_edit&id=<?php echo e($c['id']); ?>" class="fw-semibold text-decoration-none">
                                <?php echo e($c['name']); ?>
                            </a>
                            <?php if ($c['address']): ?>
                            <br><small class="text-muted"><i class="fa fa-map-marker-alt me-1"></i><?php echo e($c['address']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($c['phone']); ?></td>
                        <td><?php echo e($c['email']); ?></td>
                        <td>
                            <a href="?page=doctors&clinic_id=<?php echo e($c['id']); ?>" class="badge bg-secondary text-decoration-none">
                                <?php echo $c['doctor_count']; ?> doctor(s)
                            </a>
                        </td>
                        <td><?php echo $c['upcoming']; ?></td>
                        <td><small><?php echo e($c['timezone'] ?: 'UTC'); ?></small></td>
                        <td>
                            <?php if ($c['enabled']): ?>
                            <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge text-bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (Auth::can('clinic.edit')): ?>
                            <a href="?page=clinic_edit&id=<?php echo e($c['id']); ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fa fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (Auth::can('clinic.delete')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="confirmDeleteOne('<?php echo e($c['id']); ?>')">
                                <i class="fa fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($clinics) && Auth::can('clinic.delete')): ?>
            <div class="p-3 border-top bg-light d-flex gap-2">
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
document.getElementById('chk_all').addEventListener('change', function() {
    document.querySelectorAll('.row-chk').forEach(c => c.checked = this.checked);
});
function bulkDelete() {
    var checked = document.querySelectorAll('.row-chk:checked');
    if (!checked.length) { alert('Select at least one clinic.'); return; }
    if (confirm('Delete ' + checked.length + ' clinic(s)? This will also remove their doctors, schedules, and appointments.')) {
        document.getElementById('bulk_form').submit();
    }
}
function confirmDeleteOne(id) {
    if (confirm('Delete this clinic?')) {
        document.querySelectorAll('.row-chk').forEach(c => c.checked = false);
        var chk = document.querySelector('.row-chk[value="' + id + '"]');
        if (chk) { chk.checked = true; }
        document.getElementById('bulk_form').submit();
    }
}
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
