<?php
if (Auth::check()) {
    redirect('?page=dashboard');
}

$locale = $locale ?? 'en';
$dir = $dir ?? 'ltr';
$t = $t ?? [];
$cfg = $cfg ?? [];

$tr = function (string $key, string $fallback = '') use ($t): string {
    return isset($t[$key]) && is_string($t[$key]) && $t[$key] !== '' ? $t[$key] : $fallback;
};

$appName = $cfg['app']['name'] ?? ($cfg['app_name'] ?? 'ClinicAll');
$currentLang = in_array($locale, ['en', 'ar', 'fr', 'de'], true) ? $locale : 'en';

$subscribeUrl = '?page=subscribe&lang=' . rawurlencode($currentLang);
$loginUrl = '?page=login&lang=' . rawurlencode($currentLang);

$plans = [
    [
        'name' => $tr('home.plans.starter.name', 'Starter'),
        'badge' => $tr('home.plans.starter.badge', 'Best for small practices'),
        'price' => $tr('home.plans.starter.price', '$49'),
        'period' => $tr('home.plans.per_month', '/month'),
        'items' => [
            $tr('home.plans.starter.item_1', 'Core dashboard and team tools'),
            $tr('home.plans.starter.item_2', 'Standard reminders and messaging'),
            $tr('home.plans.starter.item_3', 'Email support'),
        ],
        'highlight' => false,
    ],
    [
        'name' => $tr('home.plans.growth.name', 'Growth'),
        'badge' => $tr('home.plans.growth.badge', 'Most popular'),
        'price' => $tr('home.plans.growth.price', '$99'),
        'period' => $tr('home.plans.per_month', '/month'),
        'items' => [
            $tr('home.plans.growth.item_1', 'Advanced workflows and reporting'),
            $tr('home.plans.growth.item_2', 'API access and integrations'),
            $tr('home.plans.growth.item_3', 'Priority support'),
        ],
        'highlight' => true,
    ],
    [
        'name' => $tr('home.plans.scale.name', 'Scale'),
        'badge' => $tr('home.plans.scale.badge', 'For multi-site organizations'),
        'price' => $tr('home.plans.scale.price', 'Custom'),
        'period' => $tr('home.plans.per_quote', 'pricing'),
        'items' => [
            $tr('home.plans.scale.item_1', 'Enterprise onboarding'),
            $tr('home.plans.scale.item_2', 'Custom integrations and SLA'),
            $tr('home.plans.scale.item_3', 'Dedicated account support'),
        ],
        'highlight' => false,
    ],
];

?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8'); ?>" dir="<?php echo htmlspecialchars($dir, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> — Subscribe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="theme-auth marketing-page">
    <div class="marketing-topbar py-2 border-bottom bg-body-tertiary">
        <div class="container-fluid px-3 px-lg-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 small text-body-secondary">
                <span class="fw-semibold"><?php echo htmlspecialchars($tr('topbar.language', 'Language'), ENT_QUOTES, 'UTF-8'); ?>:</span>
                <a class="text-decoration-none text-primary fw-semibold" href="?page=subscribe&lang=<?php echo htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tr('language.' . $currentLang, strtoupper($currentLang)), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
            <a class="small text-body-secondary text-decoration-none" href="<?php echo htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tr('topbar.login', 'Log in'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </div>

    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-lg-5 reveal-on-scroll">
                    <div class="p-4 p-lg-5 rounded-4 shadow-sm bg-body border h-100">
                        <div class="d-inline-flex align-items-center gap-2 mb-3">
                            <span class="marketing-brand__mark"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                            <span class="marketing-brand__text fw-bold"><?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <h1 class="h2 fw-bold mb-3"><?php echo htmlspecialchars($tr('pricing.title', 'Simple plans for clinics of different sizes'), ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p class="text-body-secondary mb-4"><?php echo htmlspecialchars($tr('pricing.subtitle', 'Choose a plan that fits how your team works today, with room to scale later.'), ENT_QUOTES, 'UTF-8'); ?></p>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?php echo htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary"><?php echo htmlspecialchars($tr('nav.login', 'Log in'), ENT_QUOTES, 'UTF-8'); ?></a>
                            <a href="<?php echo htmlspecialchars($subscribeUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary"><?php echo htmlspecialchars($tr('home.plans.cta', 'Choose plan'), ENT_QUOTES, 'UTF-8'); ?></a>
                        </div>

                        <hr class="my-4">

                        <div class="small text-body-secondary">
                            <?php echo htmlspecialchars($tr('contact.note', 'We respond quickly and keep setup practical for busy clinic teams.'), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="row g-4">
                        <?php foreach ($plans as $plan): ?>
                            <div class="col-md-4 reveal-on-scroll">
                                <div class="card h-100 rounded-4 shadow-sm border <?php echo $plan['highlight'] ? 'border-primary' : ''; ?>">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h2 class="h4 mb-1"><?php echo htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                                <div class="small text-body-secondary"><?php echo htmlspecialchars($plan['badge'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                            <?php if ($plan['highlight']): ?>
                                                <span class="badge text-bg-primary"><?php echo htmlspecialchars($tr('home.plans.featured', 'Most popular'), ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="display-6 fw-bold mb-3"><?php echo htmlspecialchars($plan['price'], ENT_QUOTES, 'UTF-8'); ?><span class="fs-6 fw-normal text-body-secondary"><?php echo htmlspecialchars($plan['period'], ENT_QUOTES, 'UTF-8'); ?></span></div>

                                        <ul class="list-unstyled mb-4">
                                            <?php foreach ($plan['items'] as $item): ?>
                                                <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>

                                        <a href="<?php echo htmlspecialchars($subscribeUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary mt-auto"><?php echo htmlspecialchars($tr('home.plans.cta', 'Choose plan'), ENT_QUOTES, 'UTF-8'); ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
