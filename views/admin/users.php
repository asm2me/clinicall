<?php
Auth::requireLogin();
Auth::requirePermission('user.view');
$page_title = 'User Management';
$is_super   = Auth::isSuperAdmin();
$my_tid     = Auth::tenantId();

// Bulk delete
if (is_post() && csrf_verify() && post('_action') === 'bulk_delete') {
    Auth::requirePermission('user.delete');
    $ids = array_filter(array_map('clean_uuid',(array)($_POST['ids']??[])));
    foreach ($ids as $id) {
        if ($id === Auth::userId()) { continue; } // can't delete self
        $where = $is_super ? "id=:id" : "id=:id AND tenant_id=:t";
        $params= $is_super ? [':id'=>$id] : [':id'=>$id,':t'=>$my_tid];
        Database::exec("DELETE FROM users WHERE $where", $params);
    }
    flash('User(s) deleted.');
    redirect('?page=admin_users');
}

// Filter: superadmin can filter by tenant
$filter_tenant = $is_super ? clean_uuid(get_param('tenant_id')) : $my_tid;
$search        = trim(get_param('q'));

$params = [];
$where  = '1=1';
if ($filter_tenant) { $where .= ' AND u.tenant_id=:t'; $params[':t']=$filter_tenant; }
elseif (!$is_super)  { $where .= ' AND u.tenant_id=:t'; $params[':t']=$my_tid; }
if ($search) {
    $op     = Database::likeOp();
    $where .= " AND (u.name $op :q OR u.email $op :q)";
    $params[':q']="%$search%";
}

$users = Database::all(
    "SELECT u.*, t.name AS tenant_name FROM users u LEFT JOIN tenants t ON t.id=u.tenant_id WHERE $where ORDER BY t.name,u.name",
    $params
);

$all_tenants = $is_super ? Database::all("SELECT id,name FROM tenants ORDER BY name") : [];

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-users me-2 text-primary"></i>Users</h4>
    <?php if (Auth::can('user.create')): ?>
    <a href="?page=admin_user_edit<?php echo $filter_tenant?"&tenant_id=$filter_tenant":''; ?>" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>Add User
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<form method="get" class="mb-3">
    <input type="hidden" name="page" value="admin_users">
    <div class="row g-2">
        <?php if ($is_super): ?>
        <div class="col-auto">
            <select class="form-select form-select-sm" name="tenant_id" onchange="this.form.submit()">
                <option value="">All Tenants</option>
                <?php foreach ($all_tenants as $t): ?>
                <option value="<?php echo e($t['id']); ?>" <?php echo ($filter_tenant===$t['id'])?'selected':''; ?>><?php echo e($t['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="col">
            <div class="input-group input-group-sm" style="max-width:320px;">
                <input type="text" class="form-control" name="q" placeholder="Search users…" value="<?php echo e($search); ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
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
                        <th>Name</th>
                        <th>Email</th>
                        <?php if ($is_super): ?><th>Tenant</th><?php endif; ?>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="<?php echo $is_super?8:7; ?>" class="text-center py-4 text-muted">No users found.</td></tr>
                    <?php else: foreach ($users as $u): ?>
                    <tr <?php echo ($u['id']===Auth::userId())?'class="table-active"':''; ?>>
                        <td>
                            <?php if ($u['id'] !== Auth::userId()): ?>
                            <input type="checkbox" name="ids[]" value="<?php echo e($u['id']); ?>" class="form-check-input row-chk">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=admin_user_edit&id=<?php echo e($u['id']); ?>" class="fw-semibold text-decoration-none">
                                <?php echo e($u['name']); ?>
                            </a>
                            <?php if ($u['id']===Auth::userId()): ?>
                            <span class="badge text-bg-info ms-1">You</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($u['email']); ?></td>
                        <?php if ($is_super): ?><td><?php echo e($u['tenant_name']??'<span class="text-muted">—</span>'); ?></td><?php endif; ?>
                        <td><span class="badge text-bg-secondary"><?php echo ucfirst($u['role']); ?></span></td>
                        <td><small class="text-muted"><?php echo $u['last_login'] ? fmt_date($u['last_login'],'d M Y H:i') : 'Never'; ?></small></td>
                        <td><?php echo $u['enabled'] ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Disabled</span>'; ?></td>
                        <td>
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="?page=admin_user_edit&id=<?php echo e($u['id']); ?>" class="btn btn-sm btn-outline-primary" title="Edit user">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <?php if (Auth::canImpersonate() && $u['id'] !== Auth::userId()): ?>
                                <form method="post" class="m-0">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="_action" value="impersonate_user">
                                    <input type="hidden" name="user_id" value="<?php echo e($u['id']); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Login as user">
                                        <i class="fa fa-user-secret"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($users) && Auth::can('user.delete')): ?>
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
function bulkDelete(){var c=document.querySelectorAll('.row-chk:checked');if(!c.length){alert('Select at least one user.');return;}if(confirm('Delete '+c.length+' user(s)?')){document.getElementById('bulk_form').submit();}}
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
