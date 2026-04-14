<?php
$locale = $locale ?? 'en';
$dir = $dir ?? (($locale === 'ar') ? 'rtl' : 'ltr');
$t = $t ?? [];

$appName = $cfg['app']['name'] ?? 'ClinicAll';
$appUrl = rtrim($cfg['app']['url'] ?? '', '/');
$loginUrl = '?page=login';
if (isset($_GET['lang']) && $_GET['lang'] !== '') {
    $loginUrl .= '&lang=' . urlencode((string) $_GET['lang']);
}

$locales = [
    'en' => 'English',
    'ar' => 'العربية',
    'fr' => 'Français',
    'de' => 'Deutsch',
];

$translations = function (string $key, string $fallback = '') use ($t): string {
    return isset($t[$key]) && $t[$key] !== '' ? (string) $t[$key] : $fallback;
};

$features = [
    [
        'icon' => 'fa-calendar-check',
        'title' => $translations('features.booking', 'Smart booking'),
        'text' => $translations('features.booking_text', 'Let patients reserve appointments with clear availability and less back-and-forth.'),
    ],
    [
        'icon' => 'fa-user-doctor',
        'title' => $translations('features.doctors', 'Doctor management'),
        'text' => $translations('features.doctors_text', 'Organize doctors, schedules, and specialties in a single operational view.'),
    ],
    [
        'icon' => 'fa-building',
        'title' => $translations('features.clinics', 'Clinic and branch control'),
        'text' => $translations('features.clinics_text', 'Keep clinics, locations, and tenant data structured and easy to manage.'),
    ],
    [
        'icon' => 'fa-chart-line',
        'title' => $translations('features.analytics', 'Daily visibility'),
        'text' => $translations('features.analytics_text', 'See appointments, pending tasks, and activity trends without heavy reporting tools.'),
    ],
    [
        'icon' => 'fa-bell',
        'title' => $translations('features.notifications', 'Workflow reminders'),
        'text' => $translations('features.notifications_text', 'Support teams with timely reminders for confirmations and follow-ups.'),
    ],
    [
        'icon' => 'fa-shield-heart',
        'title' => $translations('features.security', 'Secure access'),
        'text' => $translations('features.security_text', 'Role-based access keeps staff focused on what they are allowed to see and do.'),
    ],
];

$steps = [
    $translations('steps.one', 'Set up your clinics and doctors'),
    $translations('steps.two', 'Publish availability and booking options'),
    $translations('steps.three', 'Track appointments from one dashboard'),
];

$planBlocks = [
    [
        'title' => $translations('value.starter', 'Starter workflow'),
        'text' => $translations('value.starter_text', 'A simple way to launch public booking and internal scheduling.'),
    ],
    [
        'title' => $translations('value.growth', 'Growth ready'),
        'text' => $translations('value.growth_text', 'Add more clinics, teams, and appointments as your operations expand.'),
    ],
    [
        'title' => $translations('value.enterprise', 'Multi-location control'),
        'text' => $translations('value.enterprise_text', 'Built for clinic groups that need a consistent process across branches.'),
    ],
];
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($dir); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($appName); ?> — <?php echo e($translations('meta.title', 'Clinic booking and management')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo e($appUrl); ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="marketing-page">
<div class="marketing-nav border-bottom">
    <div class="container py-3 d-flex align-items-center justify-content-between gap-3">
        <a href="<?php echo e($appUrl); ?>/" class="d-flex align-items-center gap-2 text-decoration-none fw-bold">
            <i class="fa fa-hospital text-primary"></i>
            <span><?php echo e($appName); ?></span>
        </a>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <div class="language-switcher btn-group" role="group" aria-label="<?php echo e($translations('language.switcher_label', 'Language switcher')); ?>">
                <?php foreach ($locales as $code => $label): ?>
                    <?php
                    $langUrl = '?lang=' . urlencode($code);
                    if (isset($_GET['page']) && $_GET['page'] !== '') {
                        $langUrl .= '&page=' . urlencode((string) $_GET['page']);
                    }
                    ?>
                    <a class="btn btn-sm <?php echo $locale === $code ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                       href="<?php echo e($langUrl); ?>"
                       lang="<?php echo e($code); ?>"
                       <?php echo $locale === $code ? 'aria-current="true"' : ''; ?>>
                        <?php echo e($label); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <a href="<?php echo e($loginUrl); ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-right-to-bracket me-1"></i><?php echo e($translations('nav.login', 'Login')); ?>
            </a>
        </div>
    </div>
</div>

<header class="marketing-hero">
    <div class="container py-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="badge rounded-pill text-bg-light mb-3"><?php echo e($translations('hero.eyebrow', 'Clinic operations, simplified')); ?></span>
                <h1 class="display-5 fw-bold mb-3"><?php echo e($translations('hero.title', 'A modern booking experience for clinics and patients')); ?></h1>
                <p class="lead text-body-secondary mb-4"><?php echo e($translations('hero.subtitle', 'ClinicAll helps your team manage appointments, doctors, clinics, and daily operations from one clear place.')); ?></p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo e($loginUrl); ?>" class="btn btn-primary btn-lg">
                        <i class="fa fa-calendar-check me-2"></i><?php echo e($translations('hero.primary_cta', 'Get started')); ?>
                    </a>
                    <a href="#features" class="btn btn-outline-secondary btn-lg">
                        <?php echo e($translations('hero.secondary_cta', 'Explore features')); ?>
                    </a>
                </div>
                <div class="marketing-stats mt-4 row g-3">
                    <div class="col-sm-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="h3 mb-1"><?php echo e($translations('stats.one_value', '24/7')); ?></div>
                                <div class="text-body-secondary small"><?php echo e($translations('stats.one_label', 'Booking access')); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="h3 mb-1"><?php echo e($translations('stats.two_value', '1')); ?></div>
                                <div class="text-body-secondary small"><?php echo e($translations('stats.two_label', 'Unified dashboard')); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="h3 mb-1"><?php echo e($translations('stats.three_value', 'Multi')); ?></div>
                                <div class="text-body-secondary small"><?php echo e($translations('stats.three_label', 'Language ready')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="marketing-section-title h4 mb-3"><?php echo e($translations('hero.panel_title', 'Designed for busy clinic teams')); ?></h2>
                        <p class="text-body-secondary mb-4"><?php echo e($translations('hero.panel_text', 'Reduce manual follow-up, keep schedules visible, and provide patients with a smoother first impression.')); ?></p>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0 d-flex align-items-start gap-3">
                                <i class="fa fa-circle-check text-success mt-1"></i>
                                <div>
                                    <div class="fw-semibold"><?php echo e($translations('hero.panel_item_1', 'Clear appointment flow')); ?></div>
                                    <small class="text-body-secondary"><?php echo e($translations('hero.panel_item_1_text', 'From booking to confirmation in one streamlined process.')); ?></small>
                                </div>
                            </div>
                            <div class="list-group-item px-0 d-flex align-items-start gap-3">
                                <i class="fa fa-circle-check text-success mt-1"></i>
                                <div>
                                    <div class="fw-semibold"><?php echo e($translations('hero.panel_item_2', 'Team-friendly operations')); ?></div>
                                    <small class="text-body-secondary"><?php echo e($translations('hero.panel_item_2_text', 'Help staff work with less friction across clinics and branches.')); ?></small>
                                </div>
                            </div>
                            <div class="list-group-item px-0 d-flex align-items-start gap-3">
                                <i class="fa fa-circle-check text-success mt-1"></i>
                                <div>
                                    <div class="fw-semibold"><?php echo e($translations('hero.panel_item_3', 'Built for multiple languages')); ?></div>
                                    <small class="text-body-secondary"><?php echo e($translations('hero.panel_item_3_text', 'Switch between English, Arabic, French, and German support.')); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section id="features" class="marketing-features py-5">
    <div class="container">
        <div class="marketing-section-title mb-4">
            <h2 class="h3 mb-2"><?php echo e($translations('features.title', 'Feature highlights')); ?></h2>
            <p class="text-body-secondary mb-0"><?php echo e($translations('features.subtitle', 'Everything you need to present, book, and operate with confidence.')); ?></p>
        </div>

        <div class="row g-4">
            <?php foreach ($features as $feature): ?>
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 border-0 shadow-sm marketing-feature-card">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-3"><i class="fa <?php echo e($feature['icon']); ?>"></i></div>
                        <h3 class="h5"><?php echo e($feature['title']); ?></h3>
                        <p class="text-body-secondary mb-0"><?php echo e($feature['text']); ?></p>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="marketing-section py-5 bg-body-tertiary">
    <div class="container">
        <div class="marketing-section-title mb-4">
            <h2 class="h3 mb-2"><?php echo e($translations('workflow.title', 'How it works')); ?></h2>
            <p class="text-body-secondary mb-0"><?php echo e($translations('workflow.subtitle', 'A simple setup path for a smoother launch.')); ?></p>
        </div>

        <div class="row g-4">
            <?php foreach ($steps as $index => $step): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="badge text-bg-primary rounded-pill mb-3"><?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?></div>
                        <h3 class="h5"><?php echo e($step); ?></h3>
                        <p class="text-body-secondary mb-0"><?php echo e($translations('workflow.step_text', 'Use ClinicAll to keep every day organized with less effort.')); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="marketing-section py-5">
    <div class="container">
        <div class="marketing-section-title mb-4">
            <h2 class="h3 mb-2"><?php echo e($translations('value.title', 'Value that grows with your clinic')); ?></h2>
            <p class="text-body-secondary mb-0"><?php echo e($translations('value.subtitle', 'Flexible enough for a single clinic and ready for multi-location teams.')); ?></p>
        </div>

        <div class="row g-4">
            <?php foreach ($planBlocks as $plan): ?>
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-3"><?php echo e($plan['title']); ?></h3>
                        <p class="text-body-secondary mb-0"><?php echo e($plan['text']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="marketing-section py-5 bg-body-tertiary">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="marketing-section-title mb-3">
                    <h2 class="h3 mb-2"><?php echo e($translations('i18n.title', 'Built for multilingual clinics')); ?></h2>
                    <p class="text-body-secondary mb-0"><?php echo e($translations('i18n.subtitle', 'Make the public experience easier to use for patients and staff in the language they prefer.')); ?></p>
                </div>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex gap-3 mb-3">
                        <i class="fa fa-language text-primary mt-1"></i>
                        <span><?php echo e($translations('i18n.point_1', 'Interface labels can be switched for English, Arabic, French, and German.')); ?></span>
                    </li>
                    <li class="d-flex gap-3 mb-3">
                        <i class="fa fa-align-right text-primary mt-1"></i>
                        <span><?php echo e($translations('i18n.point_2', 'Right-to-left support helps Arabic content render naturally.')); ?></span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="fa fa-user-check text-primary mt-1"></i>
                        <span><?php echo e($translations('i18n.point_3', 'Patient-facing navigation stays clear and consistent across locales.')); ?></span>
                    </li>
                </ul>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="small text-body-secondary mb-2"><?php echo e($translations('language.preview', 'Language preview')); ?></div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($locales as $code => $label): ?>
                                <span class="badge rounded-pill <?php echo $locale === $code ? 'text-bg-primary' : 'text-bg-light'; ?>">
                                    <?php echo e($label); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                        <p class="mb-0 text-body-secondary"><?php echo e($translations('language.help', 'Use the switcher above to preview the public page in another language.')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="marketing-cta py-5">
    <div class="container">
        <div class="card border-0 shadow-lg">
            <div class="card-body p-4 p-lg-5 text-center">
                <h2 class="h3 mb-3"><?php echo e($translations('cta.title', 'Ready to streamline your clinic?')); ?></h2>
                <p class="text-body-secondary mb-4"><?php echo e($translations('cta.subtitle', 'Sign in to manage appointments, or switch languages to see the public experience in your preferred locale.')); ?></p>
                <div class="d-flex justify-content-center flex-wrap gap-2">
                    <a href="<?php echo e($loginUrl); ?>" class="btn btn-primary btn-lg">
                        <i class="fa fa-right-to-bracket me-2"></i><?php echo e($translations('cta.primary', 'Login to Dashboard')); ?>
                    </a>
                    <a href="#features" class="btn btn-outline-secondary btn-lg">
                        <?php echo e($translations('cta.secondary', 'Review features')); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo e($appUrl); ?>/assets/js/app.js"></script>
</body>
</html>