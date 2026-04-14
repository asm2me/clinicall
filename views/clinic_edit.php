<?php
Auth::requireLogin();
$tid  = Auth::tenantId();
if (!$tid) { flash('Select a tenant first.','warning'); redirect('?page=dashboard'); }

$id      = clean_uuid(get_param('id'));
$is_edit = (bool)$id;
Auth::requirePermission($is_edit ? 'clinic.edit' : 'clinic.create');
$page_title = $is_edit ? 'Edit Clinic' : 'Add Clinic';

$clinic = [];
if ($is_edit) {
    $clinic = Database::row("SELECT * FROM clinics WHERE id=:id AND tenant_id=:t", [':id'=>$id,':t'=>$tid]);
    if (!$clinic) { flash('Clinic not found.','danger'); redirect('?page=clinics'); }
}

$errors = [];
if (is_post() && csrf_verify()) {
    $name        = trim(post('name'));
    $description = trim(post('description'));
    $address     = trim(post('address'));
    $phone       = trim(post('phone'));
    $email       = trim(post('email'));
    $timezone    = trim(post('timezone'));
    $enabled     = (bool)post('enabled');

    if (!$name) { $errors[] = 'Clinic name is required.'; }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Invalid email address.'; }

    if (empty($errors)) {
        $data = [
            'name'        => $name,
            'description' => $description,
            'address'     => $address,
            'phone'       => $phone,
            'email'       => $email,
            'timezone'    => $timezone ?: 'UTC',
            'enabled'     => $enabled,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        if ($is_edit) {
            Database::update('clinics', $data, ['id' => $id, 'tenant_id' => $tid]);
            flash('Clinic updated successfully.');
        } else {
            $new_id = generate_uuid();
            Database::insert('clinics', array_merge($data, [
                'id'        => $new_id,
                'tenant_id' => $tid,
                'created_at'=> date('Y-m-d H:i:s'),
            ]));
            flash('Clinic added successfully.');
        }
        redirect('?page=clinics');
    }
    // Re-populate on error
    $clinic = compact('name','description','address','phone','email','timezone','enabled');
}

$timezones = DateTimeZone::listIdentifiers();
require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-hospital me-2 text-primary"></i><?php echo $page_title; ?></h4>
    <a href="?page=clinics" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo e($e); ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="post">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Clinic Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required maxlength="255"
                               value="<?php echo e($clinic['name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo e($clinic['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea class="form-control" name="address" rows="2"><?php echo e($clinic['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="tel" class="form-control" name="phone" maxlength="50"
                                   value="<?php echo e($clinic['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" maxlength="255"
                                   value="<?php echo e($clinic['email'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Timezone</label>
                        <select class="form-select" name="timezone">
                            <?php foreach ($timezones as $tz): ?>
                            <option value="<?php echo e($tz); ?>" <?php echo (($clinic['timezone'] ?? 'UTC')===$tz)?'selected':''; ?>>
                                <?php echo e($tz); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled"
                                <?php echo (!$is_edit || !empty($clinic['enabled'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-semibold" for="enabled">Active (visible to booking)</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save Clinic</button>
                        <a href="?page=clinics" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($is_edit): ?>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="mb-0">Quick Actions</h6></div>
            <div class="card-body d-grid gap-2">
                <a href="?page=doctors&clinic_id=<?php echo e($id); ?>" class="btn btn-outline-secondary text-start">
                    <i class="fa fa-user-md me-2"></i>View Doctors
                </a>
                <a href="?page=doctor_edit&clinic_id=<?php echo e($id); ?>" class="btn btn-outline-secondary text-start">
                    <i class="fa fa-plus me-2"></i>Add Doctor to This Clinic
                </a>
                <a href="?page=appointments&clinic_id=<?php echo e($id); ?>" class="btn btn-outline-secondary text-start">
                    <i class="fa fa-calendar me-2"></i>View Appointments
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once CLINICALL_ROOT . '/views/layout/footer.php'; ?>
