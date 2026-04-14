<?php
Auth::requireLogin();
if (!Auth::isSuperAdmin()) { flash('Superadmin only.','danger'); redirect('?page=dashboard'); }

$id      = clean_uuid(get_param('id'));
$is_edit = (bool)$id;
$page_title = $is_edit ? 'Edit Tenant' : 'Add Tenant';

$tenant = [];
if ($is_edit) {
    $tenant = Database::row("SELECT * FROM tenants WHERE id=:id",[':id'=>$id]);
    if (!$tenant) { flash('Tenant not found.','danger'); redirect('?page=admin_tenants'); }
}

$errors = [];
if (is_post() && csrf_verify()) {
    $name         = trim(post('name'));
    $slug         = strtolower(preg_replace('/[^a-z0-9\-]/','',str_replace(' ','-',trim(post('slug')))));
    $custom_domain= strtolower(trim(post('custom_domain')));
    $plan         = in_array(post('plan'),['standard','professional','enterprise'])?post('plan'):'standard';
    $enabled      = (bool)post('enabled');

    if (!$name) { $errors[] = 'Tenant name is required.'; }
    if (!$slug) { $errors[] = 'Slug is required (letters, numbers, hyphens only).'; }

    // Slug uniqueness
    $existing = Database::val(
        "SELECT COUNT(*) FROM tenants WHERE slug=:s" . ($is_edit ? " AND id <> :id" : ""),
        $is_edit ? [':s'=>$slug,':id'=>$id] : [':s'=>$slug]
    );
    if ((int)$existing > 0) { $errors[] = "Slug '$slug' is already in use."; }

    if (empty($errors)) {
        $data = [
            'name'          => $name,
            'slug'          => $slug,
            'custom_domain' => $custom_domain ?: null,
            'plan'          => $plan,
            'enabled'       => $enabled,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];
        if ($is_edit) {
            Database::update('tenants', $data, ['id'=>$id]);
            flash('Tenant updated.');
        } else {
            Database::insert('tenants', array_merge($data,[
                'id'         => generate_uuid(),
                'created_at' => date('Y-m-d H:i:s'),
            ]));
            flash('Tenant created.');
        }
        redirect('?page=admin_tenants');
    }
    $tenant = compact('name','slug','custom_domain','plan','enabled');
}

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-building me-2 text-primary"></i><?php echo $page_title; ?></h4>
    <a href="?page=admin_tenants" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
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
                        <label class="form-label fw-semibold">Organization Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required maxlength="255"
                               value="<?php echo e($tenant['name']??''); ?>"
                               oninput="autoSlug(this.value)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text text-muted">/t/</span>
                            <input type="text" class="form-control" name="slug" id="slug_field" required
                                   maxlength="100" pattern="[a-z0-9\-]+"
                                   placeholder="my-clinic"
                                   value="<?php echo e($tenant['slug']??''); ?>">
                        </div>
                        <div class="form-text">Used in booking URLs. Lowercase letters, numbers, hyphens only.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Custom Domain</label>
                        <input type="text" class="form-control" name="custom_domain" maxlength="255"
                               placeholder="clinic.example.com"
                               value="<?php echo e($tenant['custom_domain']??''); ?>">
                        <div class="form-text">Optional. If set, booking page resolves by this domain.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Plan</label>
                        <select class="form-select" name="plan">
                            <?php foreach (['standard'=>'Standard','professional'=>'Professional','enterprise'=>'Enterprise'] as $k=>$v): ?>
                            <option value="<?php echo $k; ?>" <?php echo (($tenant['plan']??'standard')===$k)?'selected':''; ?>><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled"
                                <?php echo (!$is_edit||!empty($tenant['enabled']))?'checked':''; ?>>
                            <label class="form-check-label fw-semibold" for="enabled">Active</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
                        <a href="?page=admin_tenants" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($is_edit): ?>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent"><h6 class="mb-0">Booking URL</h6></div>
            <div class="card-body">
                <code class="small"><?php echo e($cfg['app']['url']); ?>/booking.php?t=<?php echo e($tenant['slug']??''); ?></code>
                <a href="<?php echo e($cfg['app']['url']); ?>/booking.php?t=<?php echo e($tenant['slug']??''); ?>" target="_blank" class="btn btn-sm btn-outline-secondary ms-2">
                    <i class="fa fa-external-link"></i>
                </a>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="mb-0">Quick Actions</h6></div>
            <div class="card-body d-grid gap-2">
                <form method="post">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_action" value="switch_tenant">
                    <input type="hidden" name="tenant_id" value="<?php echo e($id); ?>">
                    <button type="submit" class="btn btn-outline-primary w-100 text-start">
                        <i class="fa fa-arrow-right-to-bracket me-2"></i>Switch to This Tenant
                    </button>
                </form>
                <a href="?page=admin_users&tenant_id=<?php echo e($id); ?>" class="btn btn-outline-secondary text-start">
                    <i class="fa fa-users me-2"></i>Manage Users
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$page_scripts = <<<JS
function autoSlug(name) {
    var f = document.getElementById('slug_field');
    if (f.dataset.manual) return;
    f.value = name.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
}
document.getElementById('slug_field').addEventListener('input', function() {
    this.dataset.manual = '1';
});
JS;
require_once CLINICALL_ROOT . '/views/layout/footer.php';
?>
