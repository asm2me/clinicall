<?php
Auth::requireLogin();
Auth::requirePermission('user.view');
$is_super = Auth::isSuperAdmin();
$my_tid   = Auth::tenantId();

$id      = clean_uuid(get_param('id'));
$is_edit = (bool)$id;
Auth::requirePermission($is_edit ? 'user.edit' : 'user.create');
$page_title = $is_edit ? 'Edit User' : 'Add User';

$user = [];
if ($is_edit) {
    $where  = $is_super ? "id=:id" : "id=:id AND tenant_id=:t";
    $params = $is_super ? [':id'=>$id] : [':id'=>$id,':t'=>$my_tid];
    $user   = Database::row("SELECT * FROM users WHERE $where", $params);
    if (!$user) { flash('User not found.','danger'); redirect('?page=admin_users'); }
}

$all_tenants = $is_super ? Database::all("SELECT id,name FROM tenants WHERE enabled=".Database::bool(true)." ORDER BY name") : [];
$all_doctors = [];
$preselect_tenant = $is_super ? clean_uuid(get_param('tenant_id')) : $my_tid;
$sel_tenant = $user['tenant_id'] ?? $preselect_tenant;
if ($sel_tenant) {
    $all_doctors = Database::all(
        "SELECT id, name FROM doctors WHERE tenant_id=:t AND enabled=".Database::bool(true)." ORDER BY name",
        [':t'=>$sel_tenant]
    );
}

$roles = $is_super
    ? ['superadmin'=>'Superadmin','admin'=>'Admin','staff'=>'Staff','doctor'=>'Doctor']
    : ['admin'=>'Admin','staff'=>'Staff','doctor'=>'Doctor'];

$errors = [];
if (is_post() && csrf_verify()) {
    $tenant_id = $is_super ? clean_uuid(post('tenant_id')) : $my_tid;
    $name      = trim(post('name'));
    $email     = strtolower(trim(post('email')));
    $role      = array_key_exists(post('role'),$roles) ? post('role') : 'staff';
    $doctor_id = $role==='doctor' ? clean_uuid(post('doctor_id')) : null;
    $password  = post('password');
    $enabled   = (bool)post('enabled');

    if (!$name)  { $errors[] = 'Name is required.'; }
    if (!$email) { $errors[] = 'Email is required.'; }
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $errors[] = 'Invalid email.'; }
    if (!$is_edit && !$password) { $errors[] = 'Password is required for new users.'; }
    if ($password && strlen($password) < 8) { $errors[] = 'Password must be at least 8 characters.'; }
    if ($role !== 'superadmin' && !$tenant_id) { $errors[] = 'Tenant is required.'; }

    // Email uniqueness
    $dup_where  = $is_edit ? "email=:e AND id <> :id" : "email=:e";
    $dup_params = $is_edit ? [':e'=>$email,':id'=>$id] : [':e'=>$email];
    if ((int)Database::val("SELECT COUNT(*) FROM users WHERE $dup_where", $dup_params) > 0) {
        $errors[] = "Email '$email' is already registered.";
    }

    if (empty($errors)) {
        $data = [
            'tenant_id'  => $tenant_id ?: null,
            'name'       => $name,
            'email'      => $email,
            'role'       => $role,
            'doctor_id'  => $doctor_id,
            'enabled'    => $enabled,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($password) { $data['password'] = Auth::hashPassword($password); }

        if ($is_edit) {
            Database::update('users', $data, ['id'=>$id]);
            flash('User updated.');
        } else {
            Database::insert('users', array_merge($data,[
                'id'        => generate_uuid(),
                'created_at'=> date('Y-m-d H:i:s'),
            ]));
            flash('User created.');
        }
        redirect('?page=admin_users');
    }
    $user = compact('tenant_id','name','email','role','doctor_id','enabled');
}

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-user me-2 text-primary"></i><?php echo $page_title; ?></h4>
    <a href="?page=admin_users" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
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
                    <?php if ($is_super): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tenant</label>
                        <select class="form-select" name="tenant_id" onchange="loadDoctors(this.value)">
                            <option value="">— None (Superadmin only) —</option>
                            <?php foreach ($all_tenants as $t): ?>
                            <option value="<?php echo e($t['id']); ?>" <?php echo ($sel_tenant===$t['id'])?'selected':''; ?>><?php echo e($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required maxlength="255"
                               value="<?php echo e($user['name']??''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required maxlength="255"
                               value="<?php echo e($user['email']??''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?php echo $is_edit ? 'New Password (leave blank to keep)' : 'Password'; ?> <?php echo $is_edit?'':'<span class="text-danger">*</span>'; ?></label>
                        <input type="password" class="form-control" name="password"
                               <?php echo $is_edit?'':'required'; ?> minlength="8"
                               placeholder="Min. 8 characters">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select class="form-select" name="role" onchange="toggleDoctorField(this.value)">
                            <?php foreach ($roles as $k=>$v): ?>
                            <option value="<?php echo $k; ?>" <?php echo (($user['role']??'staff')===$k)?'selected':''; ?>><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="doctor_field" style="<?php echo (($user['role']??'')==='doctor')?'':'display:none;'; ?>">
                        <label class="form-label fw-semibold">Linked Doctor Profile</label>
                        <select class="form-select" name="doctor_id" id="doctor_select">
                            <option value="">— None —</option>
                            <?php foreach ($all_doctors as $d): ?>
                            <option value="<?php echo e($d['id']); ?>" <?php echo (($user['doctor_id']??'')===$d['id'])?'selected':''; ?>><?php echo e($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Optional: link to a doctor record so the user can see their own schedule.</div>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled"
                                <?php echo (!$is_edit||!empty($user['enabled']))?'checked':''; ?>>
                            <label class="form-check-label fw-semibold" for="enabled">Active</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
                        <a href="?page=admin_users" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$all_doctors_json = json_encode(
    Database::all("SELECT id,name,tenant_id FROM doctors WHERE tenant_id IS NOT NULL ORDER BY name"),
    JSON_HEX_TAG
);
$page_scripts = <<<JS
var ALL_DOCTORS = $all_doctors_json;
function toggleDoctorField(role) {
    document.getElementById('doctor_field').style.display = (role==='doctor') ? '' : 'none';
}
function loadDoctors(tenantId) {
    var sel = document.getElementById('doctor_select');
    if (!sel) return;
    sel.innerHTML = '<option value="">— None —</option>';
    ALL_DOCTORS.filter(d => d.tenant_id === tenantId).forEach(function(d) {
        sel.appendChild(new Option(d.name, d.id));
    });
}
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
