<?php
Auth::requireLogin();
if (!Auth::isSuperAdmin()) { flash('Superadmin only.','danger'); redirect('?page=dashboard'); }
$page_title = 'Tenant Management';

// Bulk delete
if (is_post() && csrf_verify() && post('_action') === 'bulk_delete') {
    $ids = array_filter(array_map('clean_uuid',(array)($_POST['ids']??[])));
    foreach ($ids as $id) {
        Database::exec("DELETE FROM tenants WHERE id=:id",[':id'=>$id]);
    }
    flash(count($ids) . ' tenant(s) deleted.');
    redirect('?page=admin_tenants');
}

// Toggle enabled
if (is_post() && csrf_verify() && post('_action') === 'toggle') {
    $id      = clean_uuid(post('id'));
    $current = Database::val("SELECT enabled FROM tenants WHERE id=:id",[':id'=>$id]);
    $new_val = !$current;
    Database::exec("UPDATE tenants SET enabled=:e, updated_at=NOW() WHERE id=:id",[':e'=>$new_val,':id'=>$id]);
    flash('Tenant ' . ($new_val ? 'enabled' : 'disabled') . '.');
    redirect('?page=admin_tenants');
}

$search = trim(get_param('q'));
$params = [];
$where  = '1=1';
if ($search) {
    $op     = Database::likeOp();
    $where  = "(t.name $op :q OR t.slug $op :q OR t.custom_domain $op :q)";
    $params = [':q'=>"%$search%"];
}

$tenants = Database::all(
    "SELECT t.*,
        (SELECT COUNT(*) FROM clinics     c WHERE c.tenant_id=t.id) AS clinic_count,
        (SELECT COUNT(*) FROM users       u WHERE u.tenant_id=t.id) AS user_count,
        (SELECT COUNT(*) FROM appointments a WHERE a.tenant_id=t.id) AS appt_count
     FROM tenants t WHERE $where ORDER BY t.name",
    $params
);

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-building me-2 text-primary"></i>Tenants</h4>
    <a href="?page=admin_tenant_edit" class="btn btn-primary"><i class="fa fa-plus me-1"></i>Add Tenant</a>
</div>

<form method="get" class="mb-3">
    <input type="hidden" name="page" value="admin_tenants">
    <div class="input-group" style="max-width:380px;">
        <input type="text" class="form-control" name="q" placeholder="Search tenants…" value="<?php echo e($search); ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
        <?php if ($search): ?><a href="?page=admin_tenants" class="btn btn-outline-danger"><i class="fa fa-times"></i></a><?php endif; ?>
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
                        <th>Tenant Name</th>
                        <th>Slug</th>
                        <th>Custom Domain</th>
                        <th>Plan</th>
                        <th>Clinics</th>
                        <th>Users</th>
                        <th>Appointments</th>
                        <th>Status</th>
                        <th width="130"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($tenants)): ?>
                    <tr><td colspan="10" class="text-center py-5 text-muted">
                        <i class="fa fa-building fa-2x mb-2"></i><br>No tenants found.
                        <a href="?page=admin_tenant_edit">Add the first one</a>
                    </td></tr>
                    <?php else: foreach ($tenants as $t): ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?php echo e($t['id']); ?>" class="form-check-input row-chk"></td>
                        <td>
                            <a href="?page=admin_tenant_edit&id=<?php echo e($t['id']); ?>" class="fw-semibold text-decoration-none">
                                <?php echo e($t['name']); ?>
                            </a>
                        </td>
                        <td><code><?php echo e($t['slug']); ?></code></td>
                        <td><?php echo e($t['custom_domain'] ?: '—'); ?></td>
                        <td><span class="badge text-bg-light text-dark border"><?php echo ucfirst($t['plan']??'standard'); ?></span></td>
                        <td><?php echo $t['clinic_count']; ?></td>
                        <td><?php echo $t['user_count']; ?></td>
                        <td><?php echo $t['appt_count']; ?></td>
                        <td>
                            <?php if ($t['enabled']): ?>
                            <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge text-bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=admin_tenant_edit&id=<?php echo e($t['id']); ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fa fa-edit"></i></a>
                            <!-- Toggle -->
                            <form method="post" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="_action" value="toggle">
                                <input type="hidden" name="id" value="<?php echo e($t['id']); ?>">
                                <button type="submit" class="btn btn-sm <?php echo $t['enabled']?'btn-outline-warning':'btn-outline-success'; ?>" title="<?php echo $t['enabled']?'Disable':'Enable'; ?>">
                                    <i class="fa fa-<?php echo $t['enabled']?'ban':'check'; ?>"></i>
                                </button>
                            </form>
                            <!-- Switch to -->
                            <form method="post" style="display:inline;" title="Switch to this tenant">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="_action" value="switch_tenant">
                                <input type="hidden" name="tenant_id" value="<?php echo e($t['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-outline-info ms-1"><i class="fa fa-arrow-right-to-bracket"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($tenants)): ?>
            <div class="p-3 border-top bg-light">
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()">
                    <i class="fa fa-trash me-1"></i>Delete Selected
                </button>
                <small class="text-muted ms-3">Warning: deleting a tenant removes ALL its data.</small>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php
$page_scripts = <<<JS
document.getElementById('chk_all').addEventListener('change',function(){document.querySelectorAll('.row-chk').forEach(c=>c.checked=this.checked);});
function bulkDelete(){
    var c=document.querySelectorAll('.row-chk:checked');
    if(!c.length){alert('Select at least one tenant.');return;}
    if(confirm('PERMANENTLY delete '+c.length+' tenant(s) and ALL their clinics, doctors, and appointments?')){
        document.getElementById('bulk_form').submit();
    }
}
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
