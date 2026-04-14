<?php
$translations = $translations ?? function (string $key, string $default = ''): string {
    return $default;
};

$locale = $locale ?? 'en';
$dir = $dir ?? 'ltr';
$t = $t ?? [];
$cfg = $cfg ?? [];

$tr = function (string $key, string $fallback = '') use ($t): string {
    return isset($t[$key]) && is_string($t[$key]) && $t[$key] !== '' ? $t[$key] : $fallback;
};

$appName = $cfg['app_name'] ?? 'ClinicAll';
$currentLang = in_array($locale, ['en', 'ar', 'fr', 'de'], true) ? $locale : 'en';
$baseLangUrl = static function (string $code): string {
    return '?lang=' . rawurlencode($code);
};

$topLanguages = [
    'en' => $tr('language.en', 'English'),
    'ar' => $tr('language.ar', 'العربية'),
    'fr' => $tr('language.fr', 'Français'),
    'de' => $tr('language.de', 'Deutsch'),
];

$navItems = [
    ['href' => '#features', 'label' => $tr('nav.features', 'Features')],
    ['href' => '#how-it-works', 'label' => $tr('nav.how_it_works', 'How it works')],
    ['href' => '#integrations', 'label' => $tr('nav.integrations', 'APIs & integrations')],
    ['href' => '#downloads', 'label' => $tr('nav.downloads', 'Downloads')],
    ['href' => '#pricing', 'label' => $tr('nav.pricing', 'Pricing')],
    ['href' => '#faq', 'label' => $tr('nav.faq', 'FAQ')],
    ['href' => '#contact', 'label' => $tr('nav.contact', 'Contact')],
];

$loginUrl = $cfg['login_url'] ?? ($cfg['signin_url'] ?? '#login');
if (strpos($loginUrl, '?') === false) {
    $loginUrl .= '?lang=' . rawurlencode($currentLang);
} else {
    $loginUrl .= '&lang=' . rawurlencode($currentLang);
}

$signupUrl = $cfg['signup_url'] ?? '#register';
if (strpos($signupUrl, '?') === false) {
    $signupUrl .= '?lang=' . rawurlencode($currentLang);
} else {
    $signupUrl .= '&lang=' . rawurlencode($currentLang);
}

$contactPhone = $cfg['support_phone'] ?? $tr('contact.phone', '+1 (555) 020-2024');
$contactEmail = $cfg['support_email'] ?? $tr('contact.email', 'support@clinicall.local');

$heroStats = [
    ['value' => '24/7', 'label' => $tr('home.hero.stat_1', 'Care coordination support')],
    ['value' => '99.9%', 'label' => $tr('home.hero.stat_2', 'Uptime target for always-on teams')],
    ['value' => '40+', 'label' => $tr('home.hero.stat_3', 'Integrations and workflows ready')],
    ['value' => '120k', 'label' => $tr('home.hero.stat_4', 'Patient journeys supported')],
];

$trustLogos = [
    $tr('home.trust.logo_1', 'Clinics'),
    $tr('home.trust.logo_2', 'Hospitals'),
    $tr('home.trust.logo_3', 'Labs'),
    $tr('home.trust.logo_4', 'Pharmacies'),
    $tr('home.trust.logo_5', 'Care Teams'),
];

$features = [
    [
        'icon' => 'fa-solid fa-calendar-check',
        'title' => $tr('home.features.1.title', 'Scheduling that stays organized'),
        'text' => $tr('home.features.1.text', 'Coordinate appointments, follow-ups, and reminders from one secure workspace.'),
    ],
    [
        'icon' => 'fa-solid fa-shield-heart',
        'title' => $tr('home.features.2.title', 'Patient-first communication'),
        'text' => $tr('home.features.2.text', 'Keep messages clear, timely, and consistent across staff and patient touchpoints.'),
    ],
    [
        'icon' => 'fa-solid fa-chart-line',
        'title' => $tr('home.features.3.title', 'Operational visibility'),
        'text' => $tr('home.features.3.text', 'Track queues, workloads, and performance to improve service delivery.'),
    ],
    [
        'icon' => 'fa-solid fa-language',
        'title' => $tr('home.features.4.title', 'Multilingual by design'),
        'text' => $tr('home.features.4.text', 'Serve teams and patients in English, Arabic, French, and German.'),
    ],
    [
        'icon' => 'fa-solid fa-plug-circle-bolt',
        'title' => $tr('home.features.5.title', 'Simple integrations'),
        'text' => $tr('home.features.5.text', 'Connect your existing tools with APIs, webhooks, and SSO-ready access.'),
    ],
    [
        'icon' => 'fa-solid fa-lock',
        'title' => $tr('home.features.6.title', 'Security-minded workflows'),
        'text' => $tr('home.features.6.text', 'Use role-based access and audit-friendly controls that fit healthcare operations.'),
    ],
];

$steps = [
    [
        'number' => '01',
        'title' => $tr('home.steps.1.title', 'Set up your workspace'),
        'text' => $tr('home.steps.1.text', 'Configure locations, teams, and permissions in a few guided steps.'),
    ],
    [
        'number' => '02',
        'title' => $tr('home.steps.2.title', 'Connect your channels'),
        'text' => $tr('home.steps.2.text', 'Bring forms, notifications, and integrations together in one place.'),
    ],
    [
        'number' => '03',
        'title' => $tr('home.steps.3.title', 'Grow with confidence'),
        'text' => $tr('home.steps.3.text', 'Scale across departments, locations, and workflows without losing control.'),
    ],
];

$integrations = [
    [
        'title' => $tr('home.integrations.1.title', 'REST APIs'),
        'text' => $tr('home.integrations.1.text', 'Automate records, appointments, notifications, and reporting.'),
    ],
    [
        'title' => $tr('home.integrations.2.title', 'Webhook events'),
        'text' => $tr('home.integrations.2.text', 'Receive live updates when key operational events occur.'),
    ],
    [
        'title' => $tr('home.integrations.3.title', 'Identity & SSO'),
        'text' => $tr('home.integrations.3.text', 'Integrate with your identity provider for secure staff access.'),
    ],
];

$downloads = [
    [
        'icon' => 'fa-solid fa-globe',
        'title' => $tr('home.downloads.1.title', 'Web portal'),
        'text' => $tr('home.downloads.1.text', 'Access ClinicAll from any modern browser with no installation required.'),
    ],
    [
        'icon' => 'fa-solid fa-mobile-screen-button',
        'title' => $tr('home.downloads.2.title', 'Mobile-ready experience'),
        'text' => $tr('home.downloads.2.text', 'Use responsive layouts that keep teams productive on the go.'),
    ],
    [
        'icon' => 'fa-solid fa-desktop',
        'title' => $tr('home.downloads.3.title', 'Desktop-friendly views'),
        'text' => $tr('home.downloads.3.text', 'Give reception, admin, and operations staff a clean working surface.'),
    ],
];

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

$faqs = [
    [
        'q' => $tr('home.faq.1.q', 'Can ClinicAll support multiple languages?'),
        'a' => $tr('home.faq.1.a', 'Yes. The public site and product experience can present key content in English, Arabic, French, and German.'),
    ],
    [
        'q' => $tr('home.faq.2.q', 'Does ClinicAll provide APIs?'),
        'a' => $tr('home.faq.2.a', 'Yes. ClinicAll is designed for integration with REST endpoints, webhooks, and identity systems.'),
    ],
    [
        'q' => $tr('home.faq.3.q', 'Is there a downloadable app or portal access?'),
        'a' => $tr('home.faq.3.a', 'ClinicAll offers a browser-based portal and responsive experiences for desktop and mobile use.'),
    ],
    [
        'q' => $tr('home.faq.4.q', 'Can we start small and upgrade later?'),
        'a' => $tr('home.faq.4.a', 'Absolutely. Start with a smaller plan and move to higher tiers as your team and workflows grow.'),
    ],
];

$heroBadge = $tr('home.hero.badge', 'ClinicAll platform');
$heroTitle = $tr('home.hero.title', 'Modern care coordination for every clinic');
$heroText = $tr('home.hero.text', 'ClinicAll brings communication, scheduling, integrations, and patient support into one elegant platform.');
$heroPrimary = $tr('home.hero.primary', 'Start free');
$heroSecondary = $tr('home.hero.secondary', 'View pricing');
$trustTitle = $tr('home.trust.title', 'Trusted by care teams');
$trustText = $tr('home.trust.text', 'Built for clinics, hospitals, and healthcare partners.');
$featuresLabel = $tr('home.features.label', 'Why teams choose ClinicAll');
$featuresTitle = $tr('home.features.title', 'Everything your practice needs in one product');
$stepsLabel = $tr('home.steps.label', 'How it works');
$stepsTitle = $tr('home.steps.title', 'Go from setup to scale in three simple steps');
$stepsText = $tr('home.steps.text', 'A guided onboarding flow helps your team move faster without added complexity.');
$integrationsLabel = $tr('home.integrations.label', 'APIs and integrations');
$integrationsTitle = $tr('home.integrations.title', 'Connect ClinicAll with the systems your team already uses');
$integrationsText = $tr('home.integrations.text', 'Use secure APIs and event-driven hooks to keep workflows synchronized across your tools.');
$downloadsLabel = $tr('home.downloads.label', 'Downloads and apps');
$downloadsTitle = $tr('home.downloads.title', 'Access ClinicAll wherever your team works');
$pricingLabel = $tr('home.pricing.label', 'Pricing and plans');
$pricingTitle = $tr('home.pricing.title', 'Flexible subscriptions for growing care teams');
$faqLabel = $tr('home.faq.label', 'Frequently asked questions');
$faqTitle = $tr('home.faq.title', 'Quick answers for your team');
$contactLabel = $tr('home.contact.label', 'Need help choosing a plan?');
$contactTitle = $tr('home.contact.title', 'Talk to the ClinicAll team');
$contactText = $tr('home.contact.text', 'We can help with onboarding, integrations, migration, or product questions.');
$contactEmailLabel = $tr('home.contact.email', 'Email us');
$contactCallLabel = $tr('home.contact.call', 'Call support');
$finalTitle = $tr('home.final.title', 'Build a better care experience with ClinicAll');
$finalText = $tr('home.final.text', 'Create a more connected, multilingual, and efficient patient journey today.');
$finalPrimary = $tr('home.final.primary', 'Get started');
$finalSecondary = $tr('home.final.secondary', 'Review plans');
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8'); ?>" dir="<?php echo htmlspecialchars($dir, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index,follow">
    <title><?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="marketing-page">
    <div class="marketing-topbar py-2 border-bottom bg-body-tertiary">
        <div class="container-fluid px-3 px-lg-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2 small text-body-secondary">
                <span class="fw-semibold"><?php echo htmlspecialchars($tr('topbar.language', 'Language'), ENT_QUOTES, 'UTF-8'); ?>:</span>
                <?php foreach ($topLanguages as $code => $label): ?>
                    <a class="text-decoration-none <?php echo $code === $currentLang ? 'fw-semibold text-primary' : 'text-body-secondary'; ?>" href="<?php echo htmlspecialchars($baseLangUrl($code), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="mailto:<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>" class="small text-body-secondary text-decoration-none"><?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?></a>
                <span class="small text-body-secondary">•</span>
                <a href="tel:<?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>" class="small text-body-secondary text-decoration-none"><?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
        </div>
    </div>

    <header class="marketing-nav border-bottom bg-body">
        <div class="container-fluid px-3 px-lg-4">
            <div class="d-flex align-items-center justify-content-between py-3 gap-3">
                <a class="navbar-brand fw-bold text-decoration-none" href="<?php echo htmlspecialchars($baseLangUrl($currentLang), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></a>
                <nav class="d-none d-lg-flex align-items-center gap-3 flex-wrap">
                    <?php foreach ($navItems as $item): ?>
                        <a class="text-decoration-none text-body-secondary" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endforeach; ?>
                </nav>
                <a class="btn btn-outline-primary btn-sm ms-auto" href="<?php echo htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tr('topbar.login', 'Login'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
        </div>
    </header>

    <main>
        <section class="marketing-hero py-5">
            <div class="container py-lg-3">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7 reveal-on-scroll">
                        <span class="badge rounded-pill text-bg-primary-subtle text-primary border border-primary-subtle mb-3"><?php echo htmlspecialchars($heroBadge, ENT_QUOTES, 'UTF-8'); ?></span>
                        <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p class="lead text-body-secondary mb-4"><?php echo htmlspecialchars($heroText, ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="d-flex flex-wrap gap-3 mb-4">
                            <a href="<?php echo htmlspecialchars($signupUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-lg"><?php echo htmlspecialchars($heroPrimary, ENT_QUOTES, 'UTF-8'); ?></a>
                            <a href="#pricing" class="btn btn-outline-secondary btn-lg"><?php echo htmlspecialchars($heroSecondary, ENT_QUOTES, 'UTF-8'); ?></a>
                        </div>
                        <div class="marketing-logos d-flex flex-wrap gap-2 align-items-center">
                            <?php foreach ($trustLogos as $logo): ?>
                                <span class="badge rounded-pill text-bg-light border text-body-secondary px-3 py-2"><?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-lg-5 reveal-on-scroll">
                        <div class="card border-0 shadow-lg rounded-4">
                            <div class="card-body p-4 p-lg-5">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($tr('home.dashboard.label', 'Live overview'), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($tr('home.dashboard.title', 'Clinic performance snapshot'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <span class="badge text-bg-success-subtle text-success"><?php echo htmlspecialchars($tr('home.dashboard.live', 'Live'), ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="row g-3">
                                    <?php foreach ($heroStats as $stat): ?>
                                        <div class="col-6">
                                            <div class="rounded-4 border p-3 h-100">
                                                <div class="fs-3 fw-bold mb-1"><?php echo htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="small text-body-secondary"><?php echo htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="marketing-logos py-4 border-top border-bottom bg-body-tertiary">
            <div class="container">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($trustTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($trustText, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($trustLogos as $logo): ?>
                            <span class="badge rounded-pill text-bg-body border"><?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="py-5">
            <div class="container">
                <div class="text-center mb-4 reveal-on-scroll">
                    <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($featuresLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                    <h2 class="fw-bold"><?php echo htmlspecialchars($featuresTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>
                <div class="row g-4">
                    <?php foreach ($features as $feature): ?>
                        <div class="col-md-6 col-lg-4 reveal-on-scroll">
                            <div class="card h-100 shadow-sm border-0 rounded-4">
                                <div class="card-body p-4">
                                    <div class="icon-circle mb-3"><i class="<?php echo htmlspecialchars($feature['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></div>
                                    <h3 class="h5"><?php echo htmlspecialchars($feature['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p class="text-body-secondary mb-0"><?php echo htmlspecialchars($feature['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="py-5 bg-body-tertiary">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5 reveal-on-scroll">
                        <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($stepsLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($stepsTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="text-body-secondary"><?php echo htmlspecialchars($stepsText, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <?php foreach ($steps as $step): ?>
                                <div class="col-md-4 reveal-on-scroll">
                                    <div class="card h-100 border-0 shadow-sm rounded-4">
                                        <div class="card-body p-4">
                                            <div class="badge text-bg-primary-subtle text-primary rounded-pill mb-3"><?php echo htmlspecialchars($step['number'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <h3 class="h5"><?php echo htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <p class="text-body-secondary mb-0"><?php echo htmlspecialchars($step['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="integrations" class="marketing-api py-5">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-4 reveal-on-scroll">
                        <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($integrationsLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($integrationsTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="text-body-secondary mb-0"><?php echo htmlspecialchars($integrationsText, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <?php foreach ($integrations as $card): ?>
                                <div class="col-md-4 reveal-on-scroll">
                                    <div class="card h-100 border-0 shadow-sm rounded-4">
                                        <div class="card-body p-4">
                                            <h3 class="h5"><?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <p class="text-body-secondary mb-0"><?php echo htmlspecialchars($card['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="downloads" class="marketing-downloads py-5 bg-body-tertiary">
            <div class="container">
                <div class="text-center mb-4 reveal-on-scroll">
                    <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($downloadsLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                    <h2 class="fw-bold"><?php echo htmlspecialchars($downloadsTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>
                <div class="row g-4">
                    <?php foreach ($downloads as $download): ?>
                        <div class="col-md-4 reveal-on-scroll">
                            <div class="card h-100 border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="icon-circle mb-3"><i class="<?php echo htmlspecialchars($download['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></div>
                                    <h3 class="h5"><?php echo htmlspecialchars($download['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p class="text-body-secondary mb-0"><?php echo htmlspecialchars($download['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="pricing" class="marketing-pricing py-5">
            <div class="container">
                <div class="text-center mb-4 reveal-on-scroll">
                    <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($pricingLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                    <h2 class="fw-bold mb-0"><?php echo htmlspecialchars($pricingTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>
                <div class="row g-4">
                    <?php foreach ($plans as $plan): ?>
                        <div class="col-lg-4 reveal-on-scroll">
                            <div class="card h-100 rounded-4 shadow-sm border <?php echo $plan['highlight'] ? 'border-primary' : ''; ?>">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h3 class="h4 mb-1"><?php echo htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <div class="text-body-secondary small"><?php echo htmlspecialchars($plan['badge'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                        <?php if ($plan['highlight']): ?>
                                            <span class="badge text-bg-primary"><?php echo htmlspecialchars($tr('home.plans.featured', 'Featured'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="display-6 fw-bold mb-3"><?php echo htmlspecialchars($plan['price'], ENT_QUOTES, 'UTF-8'); ?><span class="fs-6 fw-normal text-body-secondary"><?php echo htmlspecialchars($plan['period'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                                    <ul class="list-unstyled mb-4">
                                        <?php foreach ($plan['items'] as $item): ?>
                                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="<?php echo htmlspecialchars($signupUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary mt-auto"><?php echo htmlspecialchars($tr('home.plans.cta', 'Choose plan'), ENT_QUOTES, 'UTF-8'); ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="faq" class="marketing-faq py-5 bg-body-tertiary">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <div class="text-center mb-4 reveal-on-scroll">
                            <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($faqLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                            <h2 class="fw-bold"><?php echo htmlspecialchars($faqTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                        </div>
                        <div class="accordion" id="faqAccordion">
                            <?php foreach ($faqs as $index => $faq): ?>
                                <div class="accordion-item mb-3 rounded-4 overflow-hidden shadow-sm reveal-on-scroll">
                                    <h3 class="accordion-header" id="faqHeading<?php echo (int) $index; ?>">
                                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?php echo (int) $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="faqCollapse<?php echo (int) $index; ?>">
                                            <?php echo htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8'); ?>
                                        </button>
                                    </h3>
                                    <div id="faqCollapse<?php echo (int) $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="faqHeading<?php echo (int) $index; ?>" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body text-body-secondary">
                                            <?php echo htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="marketing-contact py-5">
            <div class="container">
                <div class="card border-0 shadow-sm rounded-4 reveal-on-scroll">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($contactLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($contactTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="text-body-secondary mb-0"><?php echo htmlspecialchars($contactText, ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <a href="mailto:<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary me-2 mb-2"><?php echo htmlspecialchars($contactEmailLabel, ENT_QUOTES, 'UTF-8'); ?></a>
                                <a href="tel:<?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary mb-2"><?php echo htmlspecialchars($contactCallLabel, ENT_QUOTES, 'UTF-8'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="marketing-cta py-5">
            <div class="container text-center reveal-on-scroll">
                <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($finalTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="text-body-secondary mb-4"><?php echo htmlspecialchars($finalText, ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?php echo htmlspecialchars($signupUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-lg"><?php echo htmlspecialchars($finalPrimary, ENT_QUOTES, 'UTF-8'); ?></a>
                    <a href="#pricing" class="btn btn-outline-secondary btn-lg"><?php echo htmlspecialchars($finalSecondary, ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>