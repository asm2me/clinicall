<?php
$translations = $translations ?? function (string $key, string $default = ''): string {
    return $default;
};

$locale = $locale ?? 'en';
$dir = $dir ?? 'ltr';
$t = $t ?? [];
$cfg = $cfg ?? [];

$appName = $cfg['app_name'] ?? 'ClinicAll';
$langLabel = $t['lang_label'] ?? 'Language';
$loginLabel = $t['login'] ?? 'Login';
$contactPhone = $cfg['support_phone'] ?? ($t['support_phone'] ?? '+1 (555) 020-2024');
$contactEmail = $cfg['support_email'] ?? ($t['support_email'] ?? 'support@clinicall.local');
$primaryUrl = $cfg['signup_url'] ?? '#register';

$topLanguages = [
    'en' => 'English',
    'ar' => 'العربية',
    'fr' => 'Français',
    'de' => 'Deutsch',
];

$heroStats = [
    ['value' => '24/7', 'label' => $translations('home_stat_247', 'support and coordination')],
    ['value' => '99.9%', 'label' => $translations('home_stat_uptime', 'platform uptime target')],
    ['value' => '40+', 'label' => $translations('home_stat_integrations', 'ready integrations')],
    ['value' => '120k', 'label' => $translations('home_stat_users', 'patient journeys supported')],
];

$logos = [
    $translations('home_trust_logo_1', 'Hospitals'),
    $translations('home_trust_logo_2', 'Clinics'),
    $translations('home_trust_logo_3', 'Labs'),
    $translations('home_trust_logo_4', 'Care Teams'),
    $translations('home_trust_logo_5', 'Pharmacies'),
];

$features = [
    [
        'icon' => 'fa-solid fa-shield-heart',
        'title' => $translations('home_feature_1_title', 'Patient-first workflows'),
        'text' => $translations('home_feature_1_text', 'Coordinate appointments, reminders, and follow-ups in one secure clinical hub.'),
    ],
    [
        'icon' => 'fa-solid fa-chart-line',
        'title' => $translations('home_feature_2_title', 'Operational visibility'),
        'text' => $translations('home_feature_2_text', 'Monitor queues, team activity, and service performance with clear dashboards.'),
    ],
    [
        'icon' => 'fa-solid fa-language',
        'title' => $translations('home_feature_3_title', 'Multilingual care'),
        'text' => $translations('home_feature_3_text', 'Communicate with staff and patients in English, Arabic, French, or German.'),
    ],
    [
        'icon' => 'fa-solid fa-plug-circle-bolt',
        'title' => $translations('home_feature_4_title', 'Simple integrations'),
        'text' => $translations('home_feature_4_text', 'Connect ClinicAll to your existing tools, gateways, and internal systems.'),
    ],
    [
        'icon' => 'fa-solid fa-lock',
        'title' => $translations('home_feature_5_title', 'Security by design'),
        'text' => $translations('home_feature_5_text', 'Role-based access, audit-friendly activity, and protected data handling.'),
    ],
    [
        'icon' => 'fa-solid fa-headset',
        'title' => $translations('home_feature_6_title', 'Support that stays close'),
        'text' => $translations('home_feature_6_text', 'Give teams dependable help from onboarding to daily operations.'),
    ],
];

$steps = [
    [
        'number' => '01',
        'title' => $translations('home_step_1_title', 'Set up your workspace'),
        'text' => $translations('home_step_1_text', 'Configure locations, teams, and permissions in a few minutes.'),
    ],
    [
        'number' => '02',
        'title' => $translations('home_step_2_title', 'Connect your channels'),
        'text' => $translations('home_step_2_text', 'Link phones, forms, or APIs to centralize care requests and updates.'),
    ],
    [
        'number' => '03',
        'title' => $translations('home_step_3_title', 'Coordinate and scale'),
        'text' => $translations('home_step_3_text', 'Track outcomes, improve service speed, and expand across departments.'),
    ],
];

$apiCards = [
    [
        'title' => $translations('home_api_1_title', 'REST APIs'),
        'text' => $translations('home_api_1_text', 'Automate patient records, appointments, notifications, and reporting.'),
    ],
    [
        'title' => $translations('home_api_2_title', 'Webhook events'),
        'text' => $translations('home_api_2_text', 'Receive real-time updates when key clinical or operational events occur.'),
    ],
    [
        'title' => $translations('home_api_3_title', 'SSO and identity'),
        'text' => $translations('home_api_3_text', 'Integrate with your identity provider for secure staff access.'),
    ],
];

$downloads = [
    [
        'platform' => $translations('home_download_web_title', 'Web portal'),
        'text' => $translations('home_download_web_text', 'Access ClinicAll from any modern browser with no installation.'),
        'icon' => 'fa-solid fa-globe',
    ],
    [
        'platform' => $translations('home_download_mobile_title', 'Mobile apps'),
        'text' => $translations('home_download_mobile_text', 'Keep teams connected on the go with responsive mobile experiences.'),
        'icon' => 'fa-solid fa-mobile-screen-button',
    ],
    [
        'platform' => $translations('home_download_desktop_title', 'Desktop tools'),
        'text' => $translations('home_download_desktop_text', 'Use desktop-friendly views for reception, operations, and administration.'),
        'icon' => 'fa-solid fa-desktop',
    ],
];

$plans = [
    [
        'name' => $translations('home_plan_basic_name', 'Starter'),
        'priceMonthly' => $translations('home_plan_basic_monthly', '$49'),
        'priceYearly' => $translations('home_plan_basic_yearly', '$499'),
        'badge' => $translations('home_plan_basic_badge', 'Best for small practices'),
        'items' => [
            $translations('home_plan_basic_item_1', 'Core dashboard and team tools'),
            $translations('home_plan_basic_item_2', 'Standard messaging and reminders'),
            $translations('home_plan_basic_item_3', 'Email support'),
        ],
    ],
    [
        'name' => $translations('home_plan_growth_name', 'Growth'),
        'priceMonthly' => $translations('home_plan_growth_monthly', '$99'),
        'priceYearly' => $translations('home_plan_growth_yearly', '$999'),
        'badge' => $translations('home_plan_growth_badge', 'Most popular'),
        'highlight' => true,
        'items' => [
            $translations('home_plan_growth_item_1', 'Advanced workflows and reporting'),
            $translations('home_plan_growth_item_2', 'API access and integrations'),
            $translations('home_plan_growth_item_3', 'Priority support'),
        ],
    ],
    [
        'name' => $translations('home_plan_scale_name', 'Scale'),
        'priceMonthly' => $translations('home_plan_scale_monthly', 'Custom'),
        'priceYearly' => $translations('home_plan_scale_yearly', 'Custom'),
        'badge' => $translations('home_plan_scale_badge', 'For multi-site organizations'),
        'items' => [
            $translations('home_plan_scale_item_1', 'Enterprise onboarding'),
            $translations('home_plan_scale_item_2', 'Custom integrations and SLA'),
            $translations('home_plan_scale_item_3', 'Dedicated account support'),
        ],
    ],
];

$faqs = [
    [
        'q' => $translations('home_faq_1_q', 'Can ClinicAll support multiple languages?'),
        'a' => $translations('home_faq_1_a', 'Yes. The public site and product experience can present key content in English, Arabic, French, and German.'),
    ],
    [
        'q' => $translations('home_faq_2_q', 'Does ClinicAll provide APIs?'),
        'a' => $translations('home_faq_2_a', 'Yes. ClinicAll is designed for integration with REST endpoints, webhooks, and identity systems.'),
    ],
    [
        'q' => $translations('home_faq_3_q', 'Is there a downloadable app or portal access?'),
        'a' => $translations('home_faq_3_a', 'ClinicAll offers a browser-based portal and can extend to mobile and desktop-friendly experiences.'),
    ],
    [
        'q' => $translations('home_faq_4_q', 'Can we start small and upgrade later?'),
        'a' => $translations('home_faq_4_a', 'Absolutely. Start with a starter plan and move to higher tiers as your team and workflows grow.'),
    ],
];

$billingLabel = $translations('home_billing_label', 'Choose monthly or yearly billing');
$monthlyLabel = $translations('home_billing_monthly', 'Monthly');
$yearlyLabel = $translations('home_billing_yearly', 'Yearly');
$heroTitle = $translations('home_hero_title', 'Modern care coordination for every clinic');
$heroText = $translations('home_hero_text', 'ClinicAll brings communication, scheduling, integrations, and patient support into one elegant platform.');
$heroPrimary = $translations('home_hero_primary', 'Start free');
$heroSecondary = $translations('home_hero_secondary', 'View pricing');
?>
<div class="marketing-topbar py-2 border-bottom bg-body-tertiary">
    <div class="container-fluid px-3 px-lg-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex flex-wrap align-items-center gap-2 small text-body-secondary">
            <span class="fw-semibold"><?php echo htmlspecialchars($langLabel, ENT_QUOTES, 'UTF-8'); ?>:</span>
            <?php foreach ($topLanguages as $code => $label): ?>
                <a class="text-decoration-none <?php echo $code === $locale ? 'fw-semibold text-primary' : 'text-body-secondary'; ?>" href="?lang=<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>" class="small text-body-secondary text-decoration-none"><?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?></a>
            <span class="small text-body-secondary">•</span>
            <a href="<?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>" class="small text-body-secondary text-decoration-none"><?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </div>
</div>

<section class="hero-section py-5">
    <div class="container py-lg-3">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge rounded-pill text-bg-primary-subtle text-primary border border-primary-subtle mb-3"><?php echo htmlspecialchars($translations('home_hero_badge', 'ClinicAll platform'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="lead text-body-secondary mb-4"><?php echo htmlspecialchars($heroText, ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="<?php echo htmlspecialchars($primaryUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-lg"><?php echo htmlspecialchars($heroPrimary, ENT_QUOTES, 'UTF-8'); ?></a>
                    <a href="#pricing" class="btn btn-outline-secondary btn-lg"><?php echo htmlspecialchars($heroSecondary, ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
                <div class="marketing-logos d-flex flex-wrap gap-3 align-items-center">
                    <?php foreach ($logos as $logo): ?>
                        <span class="badge rounded-pill text-bg-light border text-body-secondary px-3 py-2"><?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($translations('home_dashboard_label', 'Live overview'), ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="fw-semibold"><?php echo htmlspecialchars($translations('home_dashboard_title', 'Clinic performance snapshot'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <span class="badge text-bg-success-subtle text-success"><?php echo htmlspecialchars($translations('home_dashboard_live', 'Live'), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($heroStats as $stat): ?>
                                <div class="col-6">
                                    <div class="stat-card rounded-4 border p-3 h-100">
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
                <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($translations('home_trust_title', 'Trusted by care teams'), ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="fw-semibold"><?php echo htmlspecialchars($translations('home_trust_text', 'Built for clinics, hospitals, and healthcare partners.'), ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($logos as $logo): ?>
                    <span class="badge rounded-pill text-bg-body border"><?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($translations('home_features_label', 'Why teams choose ClinicAll'), ENT_QUOTES, 'UTF-8'); ?></div>
            <h2 class="fw-bold"><?php echo htmlspecialchars($translations('home_features_title', 'Everything your practice needs in one product'), ENT_QUOTES, 'UTF-8'); ?></h2>
        </div>
        <div class="row g-4">
            <?php foreach ($features as $feature): ?>
                <div class="col-md-6 col-lg-4">
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

<section class="py-5 bg-body-tertiary">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-5">
                <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($translations('home_steps_label', 'How it works'), ENT_QUOTES, 'UTF-8'); ?></div>
                <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($translations('home_steps_title', 'Go from setup to scale in three simple steps'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="text-body-secondary"><?php echo htmlspecialchars($translations('home_steps_text', 'A guided onboarding flow helps your team move faster without added complexity.'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <?php foreach ($steps as $step): ?>
                        <div class="col-md-4">
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

<section class="marketing-api py-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-4">
                <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($translations('home_api_label', 'APIs and integrations'), ENT_QUOTES, 'UTF-8'); ?></div>
                <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($translations('home_api_title', 'Connect ClinicAll with the systems your team already uses'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="text-body-secondary mb-0"><?php echo htmlspecialchars($translations('home_api_text', 'Use secure APIs and event-driven hooks to keep workflows synchronized across your tools.'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="col-lg-8">
                <div class="row g-3">
                    <?php foreach ($apiCards as $card): ?>
                        <div class="col-md-4">
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

<section class="marketing-downloads py-5 bg-body-tertiary">
    <div class="container">
        <div class="text-center mb-4">
            <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($translations('home_downloads_label', 'Downloads and apps'), ENT_QUOTES, 'UTF-8'); ?></div>
            <h2 class="fw-bold"><?php echo htmlspecialchars($translations('home_downloads_title', 'Access ClinicAll wherever your team works'), ENT_QUOTES, 'UTF-8'); ?></h2>
        </div>
        <div class="row g-4">
            <?php foreach ($downloads as $download): ?>
                <div class="col-md-4">
                    <div class="marketing-app-card card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="icon-circle mb-3"><i class="<?php echo htmlspecialchars($download['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></div>
                            <h3 class="h5"><?php echo htmlspecialchars($download['platform'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="text-body-secondary mb-0"><?php echo htmlspecialchars($download['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="pricing" class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($translations('home_pricing_label', 'Pricing and plans'), ENT_QUOTES, 'UTF-8'); ?></div>
            <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($translations('home_pricing_title', 'Flexible subscriptions for growing care teams'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="marketing-plan-switch d-inline-flex align-items-center gap-2 badge text-bg-light border px-3 py-2">
                <span><?php echo htmlspecialchars($billingLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="text-body-secondary"><?php echo htmlspecialchars($monthlyLabel, ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($yearlyLabel, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($plans as $plan): ?>
                <div class="col-lg-4">
                    <div class="card h-100 rounded-4 shadow-sm border <?php echo !empty($plan['highlight']) ? 'border-primary' : ''; ?>">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="h4 mb-1"><?php echo htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <div class="text-body-secondary small"><?php echo htmlspecialchars($plan['badge'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <?php if (!empty($plan['highlight'])): ?>
                                    <span class="badge text-bg-primary"><?php echo htmlspecialchars($translations('home_plan_featured', 'Featured'), ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="display-6 fw-bold mb-3"><?php echo htmlspecialchars($plan['priceMonthly'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <ul class="list-unstyled mb-4">
                                <?php foreach ($plan['items'] as $item): ?>
                                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?php echo htmlspecialchars($primaryUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary mt-auto"><?php echo htmlspecialchars($translations('home_plan_cta', 'Choose plan'), ENT_QUOTES, 'UTF-8'); ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="marketing-faq py-5 bg-body-tertiary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="text-center mb-4">
                    <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($translations('home_faq_label', 'Frequently asked questions'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <h2 class="fw-bold"><?php echo htmlspecialchars($translations('home_faq_title', 'Quick answers for your team'), ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>
                <div class="accordion" id="faqAccordion">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="marketing-faq-item accordion-item mb-3 rounded-4 overflow-hidden shadow-sm">
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

<section class="marketing-contact py-5">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="text-uppercase small text-body-secondary"><?php echo htmlspecialchars($translations('home_contact_label', 'Need help choosing a plan?'), ENT_QUOTES, 'UTF-8'); ?></div>
                        <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($translations('home_contact_title', 'Talk to the ClinicAll team'), ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="text-body-secondary mb-0"><?php echo htmlspecialchars($translations('home_contact_text', 'We can help with onboarding, integrations, migration, or product questions.'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="mailto:<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary me-2"><?php echo htmlspecialchars($translations('home_contact_email', 'Email us'), ENT_QUOTES, 'UTF-8'); ?></a>
                        <a href="tel:<?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary"><?php echo htmlspecialchars($translations('home_contact_call', 'Call support'), ENT_QUOTES, 'UTF-8'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($translations('home_final_cta_title', 'Build a better care experience with ClinicAll'), ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="text-body-secondary mb-4"><?php echo htmlspecialchars($translations('home_final_cta_text', 'Create a more connected, multilingual, and efficient patient journey today.'), ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?php echo htmlspecialchars($primaryUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-lg"><?php echo htmlspecialchars($translations('home_final_cta_primary', 'Get started'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#pricing" class="btn btn-outline-secondary btn-lg"><?php echo htmlspecialchars($translations('home_final_cta_secondary', 'Review plans'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </div>
</section>