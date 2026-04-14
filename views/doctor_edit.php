<?php
Auth::requireLogin();
$tid  = Auth::tenantId();
if (!$tid) { flash('Select a tenant first.','warning'); redirect('?page=dashboard'); }

$id      = clean_uuid(get_param('id'));
$is_edit = (bool)$id;
Auth::requirePermission($is_edit ? 'doctor.edit' : 'doctor.create');
$page_title = $is_edit ? 'Edit Doctor' : 'Add Doctor';

$doctor = [];
if ($is_edit) {
    $doctor = Database::row("SELECT * FROM doctors WHERE id=:id AND tenant_id=:t", [':id'=>$id,':t'=>$tid]);
    if (!$doctor) { flash('Doctor not found.','danger'); redirect('?page=doctors'); }
}

$all_clinics = Database::all(
    "SELECT id, name FROM clinics WHERE tenant_id=:t AND enabled=".Database::bool(true)." ORDER BY name",
    [':t'=>$tid]
);
$preselect_clinic = clean_uuid(get_param('clinic_id'));

$errors = [];
if (is_post() && csrf_verify()) {
    $clinic_id = clean_uuid(post('clinic_id'));
    $name      = trim(post('name'));
    $specialty = trim(post('specialty'));
    $phone     = trim(post('phone'));
    $email     = trim(post('email'));
    $bio       = trim(post('bio'));
    $enabled   = (bool)post('enabled');

    if (!$name)      { $errors[] = 'Doctor name is required.'; }
    if (!$clinic_id) { $errors[] = 'Clinic is required.'; }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Invalid email.'; }
    // Verify clinic belongs to tenant
    if ($clinic_id && !Database::row("SELECT id FROM clinics WHERE id=:id AND tenant_id=:t",[':id'=>$clinic_id,':t'=>$tid])) {
        $errors[] = 'Invalid clinic selection.';
    }

    if (empty($errors)) {
        $data = [
            'clinic_id'  => $clinic_id,
            'name'       => $name,
            'specialty'  => $specialty,
            'phone'      => $phone,
            'email'      => $email,
            'bio'        => $bio,
            'enabled'    => $enabled,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($is_edit) {
            Database::update('doctors', $data, ['id'=>$id,'tenant_id'=>$tid]);
            flash('Doctor updated.');
        } else {
            Database::insert('doctors', array_merge($data, [
                'id'        => generate_uuid(),
                'tenant_id' => $tid,
                'created_at'=> date('Y-m-d H:i:s'),
            ]));
            flash('Doctor added.');
        }
        redirect('?page=doctors' . ($clinic_id ? "&clinic_id=$clinic_id" : ''));
    }
    $doctor = compact('clinic_id','name','specialty','phone','email','bio','enabled');
}

require_once CLINICALL_ROOT . '/views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa fa-user-md me-2 text-primary"></i><?php echo $page_title; ?></h4>
    <a href="?page=doctors" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
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
                        <label class="form-label fw-semibold">Clinic <span class="text-danger">*</span></label>
                        <select class="form-select" name="clinic_id" required>
                            <option value="">— Select Clinic —</option>
                            <?php
                            $sel = $doctor['clinic_id'] ?? $preselect_clinic;
                            foreach ($all_clinics as $c): ?>
                            <option value="<?php echo e($c['id']); ?>" <?php echo ($sel===$c['id'])?'selected':''; ?>>
                                <?php echo e($c['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Doctor Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required maxlength="255"
                               value="<?php echo e($doctor['name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Specialty / Title</label>
                        <input type="text" class="form-control" name="specialty" maxlength="255"
                               placeholder="e.g. Cardiologist, General Practitioner"
                               value="<?php echo e($doctor['specialty'] ?? ''); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="tel" class="form-control" name="phone" maxlength="50"
                                   value="<?php echo e($doctor['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" maxlength="255"
                                   value="<?php echo e($doctor['email'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bio</label>
                        <textarea class="form-control" name="bio" rows="4" maxlength="2000"><?php echo e($doctor['bio'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled"
                                <?php echo (!$is_edit || !empty($doctor['enabled'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-semibold" for="enabled">Active</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
                        <a href="?page=doctors" class="btn btn-outline-secondary">Cancel</a>
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
                <a href="?page=schedules&doctor_id=<?php echo e($id); ?>" class="btn btn-outline-info text-start">
                    <i class="fa fa-clock me-2"></i>Manage Schedules
                </a>
                <a href="?page=schedule_edit&doctor_id=<?php echo e($id); ?>" class="btn btn-outline-secondary text-start">
                    <i class="fa fa-plus me-2"></i>Add Schedule Block
                </a>
                <a href="?page=exceptions&doctor_id=<?php echo e($id); ?>" class="btn btn-outline-secondary text-start">
                    <i class="fa fa-calendar-xmark me-2"></i>Manage Exceptions
                </a>
                <a href="?page=appointments&doctor_id=<?php echo e($id); ?>" class="btn btn-outline-secondary text-start">
                    <i class="fa fa-calendar-check me-2"></i>View Appointments
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once CLINICALL_ROOT . '/views/layout/footer.php'; ?>
