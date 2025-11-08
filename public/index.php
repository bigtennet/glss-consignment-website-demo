<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$config = swiftship_config();
$settings = $config['settings'] ?? [];
$brandTokens = preg_split('/\s+/', trim($settings['site_name'] ?? $config['app_name']));
$brandShort = strtoupper($brandTokens[0] ?? $config['app_name']);

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = $scriptDir === '' || $scriptDir === '.' ? '' : $scriptDir;

function url(string $path = ''): string
{
    global $basePath;

    if ($path === '') {
        return $basePath === '' ? '/' : $basePath . '/';
    }

    return ($basePath === '' ? '' : $basePath) . '/' . ltrim($path, '/');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($config['app_name']); ?> | Global logistics solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= e(url('assets/css/app.css')); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="brand" href="<?= e(url()); ?>">
                <i class="fa-solid fa-shield-halved"></i>
                <span class="brand-text">
                    <span class="brand-accent"><?= e($brandShort); ?></span>
                    <span class="brand-tagline"><?= e($settings['site_tagline'] ?? 'Global Logistics Security Co.'); ?></span>
                </span>
            </a>
            <button class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="primary-navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="nav-actions">
                <div class="nav-links" id="primary-navigation" role="navigation">
                    <a href="<?= e(url()); ?>#services" class="active">Services</a>
                    <a href="<?= e(url()); ?>#network">Network</a>
                    <a href="<?= e(url()); ?>#track">Tracking</a>
                    <a href="<?= e(url()); ?>#contact">Contact</a>
                </div>
                <a class="nav-cta" href="<?= e(url()); ?>#contact">
                    <i class="fa-solid fa-headset"></i> Request Support
                </a>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <div>
                <h1><?= e($settings['hero_heading'] ?? 'Global logistics security you can trust'); ?></h1>
                <p>
                    <?= e(
                        $settings['hero_subheading']
                        ?? ($settings['site_name'] ?? $config['app_name']) . ' delivers escorted, sensor-tracked freight for organizations that cannot compromise on custody or visibility.'
                    ); ?>
                </p>
                <div class="hero-actions">
                    <a class="btn-primary" href="<?= e(url('track.php')); ?>">
                        <i class="fa-solid fa-location-dot"></i> Track your cargo
                    </a>
                    <a class="btn-outline" href="<?= e(url()); ?>#contact">
                        <i class="fa-solid fa-headset"></i> Talk to operations
                    </a>
                </div>
            </div>
            <div>
                <img src="https://dhlsecfreights.info/media/3-column-info/logistic3-e1363946571431.jpg" alt="<?= e($config['app_name']); ?> secure freight operations" class="hero-visual">
            </div>
        </div>
    </header>

<section class="section section-metrics">
    <div class="container stats-grid">
        <div class="stats-card">
            <span><i class="fa-solid fa-shield-halved"></i>Secure lanes</span>
            <strong>180+</strong>
            <p>Certified corridors with continuous telemetry and dual-control custody.</p>
        </div>
        <div class="stats-card">
            <span><i class="fa-solid fa-plane-up"></i>Global uplift</span>
            <strong>54</strong>
            <p>Strategic air hubs with on-site GLSS mission controllers on standby.</p>
        </div>
        <div class="stats-card">
            <span><i class="fa-solid fa-stopwatch"></i>On-time</span>
            <strong>98.6%</strong>
            <p>On-time delivery performance across defence and life-science programmes.</p>
        </div>
        <div class="stats-card">
            <span><i class="fa-solid fa-user-lock"></i>Clearances</span>
            <strong>320+</strong>
            <p>Security-vetted specialists supporting diplomatic, defence, and critical infrastructure moves.</p>
        </div>
    </div>
</section>

    <main>
        <section class="section" id="track">
            <div class="container split-grid align-top">
                <div class="tracking-panel">
                    <h2>Instant consignment tracking</h2>
                    <p>Enter the <?= e($brandShort); ?> tracking number supplied on your airway bill or booking confirmation.</p>
                    <form method="get" action="<?= e(url('track.php')); ?>">
                        <label for="tracking" class="visually-hidden">Tracking number</label>
                        <input type="text"
                               id="tracking"
                               name="tracking"
                               class="form-control"
                               placeholder="e.g. GLS3A1B2C"
                               required>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-magnifying-glass-location"></i>
                            Track consignment
                        </button>
                    </form>
                    <div class="timeline">
                        <h3>Security checkpoints</h3>
                        <ul class="timeline-list">
                            <li>
                                <strong>Collection & verification</strong>
                                Chain-of-custody begins with biometric confirmation of courier and manifest.
                            </li>
                            <li>
                                <strong>Hub processing</strong>
                                Dedicated vault storage with x-ray screening and bonded clearance.
                            </li>
                            <li>
                                <strong>In-transit monitoring</strong>
                                Live telemetry and geofencing with automated variance alerts.
                            </li>
                            <li>
                                <strong>Delivery confirmation</strong>
                                Secure code hand-off and capture of electronic proof of delivery.
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="cta-banner">
                    <h3>Mission control on every corridor</h3>
                    <p>
                        Our command desk tracks every checkpoint with live variance escalation, bonded warehousing, and rapid charter availability across 190+ countries.
                    </p>
                    <a class="btn-outline" href="<?= e(url()); ?>#contact">
                        Book a secure movement
                    </a>
                </div>
            </div>
        </section>

<section class="section" id="network">
    <div class="container feature-grid">
        <article class="feature-card">
            <i class="fa-solid fa-satellite-dish"></i>
            <h3>Live telemetry & alerting</h3>
            <p>Every consignment is instrumented with sensor telemetry, geo-fencing, and automated variance escalation to the GLSS operations desk.</p>
        </article>
        <article class="feature-card">
            <i class="fa-solid fa-people-group"></i>
            <h3>Security cleared teams</h3>
            <p>Veteran-led operators coordinate escorts, bonded warehousing, and classified handovers with NATO-cleared partners on five continents.</p>
        </article>
        <div class="cta-banner">
            <h3>Ready to secure your next move?</h3>
            <p>Submit a routing brief and our mission controllers will build a compliant, end-to-end transport plan within minutes.</p>
            <a class="btn-primary" href="<?= e(url()); ?>#contact">
                <i class="fa-solid fa-paper-plane"></i> Request briefing
            </a>
        </div>
    </div>
</section>

    <section class="section" id="services" style="background: #fff;">
        <div class="container">
                <div class="section-title">
                    <span>WHAT WE DO</span>
                    <h2>Comprehensive logistics engineered for high risk cargo</h2>
                </div>
                <div class="cards-grid">
                    <article class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-truck-fast"></i></div>
                        <h3>Domestic courier solutions</h3>
                        <p>
                            Accelerated door-to-door distribution across every major city with time-definite delivery windows and verified custody.
                        </p>
                        <a href="<?= e(url()); ?>#contact">Read more</a>
                    </article>
                    <article class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-plane"></i></div>
                        <h3>International secure freight</h3>
                        <p>
                            Export controlled commodities handled end-to-end with dual-person escorting, diplomatic clearances, and bonded warehousing.
                        </p>
                        <a href="<?= e(url()); ?>#contact">Read more</a>
                    </article>
                    <article class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-warehouse"></i></div>
                        <h3>Specialist import programs</h3>
                        <p>
                            Simplify inbound shipments with proactive customs brokerage, tariff optimisation, and temperature-controlled last mile.
                        </p>
                        <a href="<?= e(url()); ?>#contact">Read more</a>
                    </article>
                </div>
            </div>
        </section>

        <section class="section" id="contact">
            <div class="container split-grid-compact align-top">
                <div>
                    <h2 class="section-title" style="text-align: left; margin-bottom: 1.5rem;">
                        <span>MISSION CONTROL</span>
                        Speak with an operations controller
                    </h2>
                    <p style="color: var(--brand-muted); line-height: 1.7;">
                        Our 24/7 command center in Newnan, GA oversees every secure lane and partner hand-off. Talk to a controller for
                        routing assessments, live variance support, or classified handling protocols.
                    </p>
                    <p style="margin-top: 1.2rem; font-weight: 600;">
                        <?php if (!empty($settings['support_phone'])): ?>
                            <i class="fa-solid fa-phone me-2"></i><?= e($settings['support_phone']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($settings['support_email'])): ?>
                            <i class="fa-solid fa-envelope me-2"></i><?= e($settings['support_email']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($settings['support_address'])): ?>
                            <i class="fa-solid fa-location-dot me-2"></i><?= nl2br(e($settings['support_address'])); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="tracking-panel">
                    <h2>Request a briefing</h2>
                    <form method="post" action="mailto:<?= e($settings['support_email'] ?? 'glss01@europe.com'); ?>">
                        <input type="text" name="name" class="form-control" placeholder="Your name" required>
                        <input type="email" name="email" class="form-control" placeholder="Business email" required style="margin-top: 0.8rem;">
                        <input type="text" name="reference" class="form-control" placeholder="Reference or tracking ID" style="margin-top: 0.8rem;">
                        <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                            <i class="fa-solid fa-paper-plane"></i> Submit request
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div>
                <a class="brand" href="<?= e(url()); ?>">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span class="brand-text">
                        <span class="brand-accent"><?= e($brandShort); ?></span>
                        <span class="brand-tagline"><?= e($settings['site_tagline'] ?? 'Global Logistics Security Co.'); ?></span>
                    </span>
                </a>
                <p style="margin-top: 1rem;">
                    A veteran-led logistics command delivering high-security freight with uncompromising visibility, compliance, and speed.
                </p>
            </div>
            <div>
                <h4>We offer</h4>
                <ul>
                    <li>Air charter & hand-carry</li>
                    <li>Secure road linehaul</li>
                    <li>Defense logistics support</li>
                    <li>High-value warehousing</li>
                    <li>Cold chain management</li>
                </ul>
            </div>
            <div>
                <h4>Main links</h4>
                <ul>
                    <li><a href="<?= e(url()); ?>#services">Services</a></li>
                    <li><a href="<?= e(url()); ?>#track">Tracking</a></li>
                    <li><a href="<?= e(url()); ?>#network">Network</a></li>
                    <li><a href="<?= e(url()); ?>#contact">Contact</a></li>
                    <li><a href="<?= e(url('admin/login.php')); ?>">Admin login</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul>
                    <?php if (!empty($settings['support_phone'])): ?>
                        <li><i class="fa-solid fa-phone me-2"></i><?= e($settings['support_phone']); ?></li>
                    <?php endif; ?>
                    <?php if (!empty($settings['support_email'])): ?>
                        <li><i class="fa-solid fa-envelope me-2"></i><?= e($settings['support_email']); ?></li>
                    <?php endif; ?>
                    <li><i class="fa-solid fa-clock me-2"></i>24/7 security command desk</li>
                    <?php if (!empty($settings['support_address'])): ?>
                        <li><i class="fa-solid fa-location-dot me-2"></i><?= nl2br(e($settings['support_address'])); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>&copy; <?= date('Y'); ?> <?= e($config['app_name']); ?>. All rights reserved.</span>
            <span>Certified secure logistics partner • ISO 28000 & TAPA TSR</span>
        </div>
    </footer>
    <script>
        const navToggle = document.getElementById('nav-toggle');
        const navLinks = document.getElementById('primary-navigation');

        if (navToggle && navLinks) {
            const closeNav = () => {
                navLinks.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
            };

            navToggle.addEventListener('click', (event) => {
                event.stopPropagation();
                const isOpen = navLinks.classList.toggle('is-open');
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            navLinks.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', closeNav);
            });

            document.querySelectorAll('.nav-cta').forEach((cta) => {
                cta.addEventListener('click', closeNav);
            });

            document.addEventListener('click', (event) => {
                if (!navLinks.contains(event.target) && !navToggle.contains(event.target)) {
                    closeNav();
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 992) {
                    closeNav();
                }
            });
        }
    </script>
</body>
</html>

