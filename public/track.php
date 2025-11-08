<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$config = swiftship_config();
$database = new SwiftShip\Database($config['database']);
$shipments = new SwiftShip\Shipments($database->getConnection());

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

$trackingQuery = isset($_GET['tracking']) ? strtoupper(trim((string) $_GET['tracking'])) : '';
$trackingResult = null;
$trackingError = null;

if ($trackingQuery !== '') {
    $trackingResult = $shipments->findByTrackingNumber($trackingQuery);

    if (!$trackingResult) {
        $trackingError = 'We could not find a consignment with that tracking number. Please verify and try again.';
    }
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
    <title><?= e($config['app_name']); ?> | Track consignment</title>
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
                    <a href="<?= e(url()); ?>#services">Services</a>
                    <a href="<?= e(url()); ?>#network">Network</a>
                    <a href="#track" class="active">Tracking</a>
                    <a href="<?= e(url()); ?>#contact">Contact</a>
                </div>
                <a class="nav-cta" href="<?= e(url()); ?>">
                    <i class="fa-solid fa-house"></i> Home
                </a>
            </div>
        </div>
    </nav>

    <header class="hero hero-compact">
        <div class="container">
            <div>
                <h1><?= e($settings['hero_heading'] ?? 'Track & verify your consignment'); ?></h1>
                <p>
                    <?= e(
                        $settings['hero_subheading']
                        ?? 'Retrieve real-time milestones, custody notes, and delivery confirmation for every '
                            . $brandShort
                            . ' movement. For urgent escalations contact the operations desk on '
                            . ($settings['support_phone'] ?? '+1 (470) 347-2702')
                            . '.'
                    ); ?>
                </p>
            </div>
        </div>
    </header>

    <main>
        <section class="section" id="track">
            <div class="container split-grid align-top">
                <div class="tracking-panel">
                    <h2>Enter your tracking number</h2>
                    <p style="color: var(--brand-muted); margin-bottom: 0.75rem;">
                        Use the <?= e($brandShort); ?> reference supplied in your dispatch confirmation email.
                    </p>
                    <form method="get">
                        <label class="visually-hidden" for="tracking">Tracking number</label>
                        <input
                            type="text"
                            id="tracking"
                            name="tracking"
                            class="form-control"
                            placeholder="e.g. GLS3A1B2C"
                            value="<?= e($trackingQuery); ?>"
                            required
                        >
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-magnifying-glass-location"></i>
                            Find consignment
                        </button>
                    </form>

                    <?php if ($trackingQuery !== ''): ?>
                        <?php if ($trackingError): ?>
                            <div class="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <?= e($trackingError); ?>
                            </div>
                        <?php else: ?>
                            <div class="result-summary">
                                <div class="pill-destination">
                                    <i class="fa-solid fa-location-dot"></i>
                                    Delivery to <strong><?= e($trackingResult['destination']); ?></strong>
                                </div>
                                <div class="pill-recipient">
                                    <i class="fa-solid fa-user-shield"></i>
                                    Recipient: <strong><?= e($trackingResult['recipient_name']); ?></strong>
                                </div>
                            </div>
                            <div class="result-wrapper">
                                <article class="result-card">
                                    <h3>Shipment details</h3>
                                    <dl>
                                        <dt>Tracking number</dt>
                                        <dd><?= e($trackingResult['tracking_number']); ?></dd>

                                        <dt>Sender</dt>
                                        <dd><?= e($trackingResult['sender_name']); ?></dd>

                                        <dt>Recipient</dt>
                                        <dd><?= e($trackingResult['recipient_name']); ?></dd>

                                        <dt>Route</dt>
                                        <dd><?= e($trackingResult['origin']); ?> &rarr; <?= e($trackingResult['destination']); ?></dd>

                                        <dt>Expected delivery</dt>
                                        <dd>
                                            <?= $trackingResult['expected_delivery']
                                                ? e(date('F j, Y', strtotime((string) $trackingResult['expected_delivery'])))
                                                : 'To be confirmed'; ?>
                                        </dd>

                                        <dt>Created</dt>
                                        <dd><?= e(date('F j, Y g:i A', strtotime($trackingResult['created_at']))); ?></dd>
                                    </dl>
                                </article>
                                <article class="result-card">
                                    <h3>Current status</h3>
                                    <p>
                                        <span class="status-pill">
                                            <i class="fa-solid fa-shield-check"></i><?= e($trackingResult['status']); ?>
                                        </span>
                                    </p>
                                    <p style="color: var(--brand-muted); margin-top: 0.75rem;">
                                        Updated <?= e(date('F j, Y g:i A', strtotime($trackingResult['updated_at']))); ?>
                                    </p>
                                    <?php if (!empty($trackingResult['notes'])): ?>
                                        <div class="notes-block">
                                            <?= nl2br(e($trackingResult['notes'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <p style="color: var(--brand-muted); font-style: italic;">
                                            No additional notes recorded for this milestone.
                                        </p>
                                    <?php endif; ?>
                                </article>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="cta-banner">
                    <h3>Need live mission support?</h3>
                    <p>Controller assistance is available 24/7 for status escalations, rerouting, or delivery confirmation.</p>
                    <a class="btn-outline" href="<?= e(url()); ?>#support-desk">
                        Contact operations
                    </a>
                </div>
            </div>
        </section>

        <section class="section" id="support-desk">
            <div class="container feature-grid">
                <article class="feature-card">
                    <i class="fa-solid fa-headset"></i>
                    <h3>24/7 command desk</h3>
                    <p>Speak directly with mission controllers for routing advice, risk assessments, or classified handling protocols.</p>
                    <p style="margin: 0; font-weight: 600;">
                        <?php if (!empty($settings['support_phone'])): ?>
                            <i class="fa-solid fa-phone me-2"></i><?= e($settings['support_phone']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($settings['support_email'])): ?>
                            <i class="fa-solid fa-envelope me-2"></i><?= e($settings['support_email']); ?>
                        <?php endif; ?>
                    </p>
                </article>
                <div class="cta-banner">
                    <h3>Request a priority call</h3>
                    <p>Submit your contact details and a GLSS controller will return your call with the next security window.</p>
                    <a class="btn-primary" href="mailto:<?= e($settings['support_email'] ?? 'glss01@europe.com'); ?>">
                        <i class="fa-solid fa-phone-volume"></i> Schedule call
                    </a>
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
                    Integrated security freight management with global coverage, military-grade compliance, and live visibility.
                </p>
            </div>
            <div>
                <h4>Support</h4>
                <ul>
                    <li><a href="<?= e(url()); ?>#services">Logistics services</a></li>
                    <li><a href="<?= e(url()); ?>#track">Track a shipment</a></li>
                    <li><a href="<?= e(url()); ?>#contact">Contact operations</a></li>
                    <li><a href="<?= e(url('admin/login.php')); ?>">Admin login</a></li>
                </ul>
            </div>
            <div>
                <h4>24/7 desk</h4>
                <?php if (!empty($settings['support_phone'])): ?>
                    <p><i class="fa-solid fa-phone me-2"></i><?= e($settings['support_phone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($settings['support_email'])): ?>
                    <p><i class="fa-solid fa-envelope me-2"></i><?= e($settings['support_email']); ?></p>
                <?php endif; ?>
                <?php if (!empty($settings['support_address'])): ?>
                    <p><i class="fa-solid fa-location-dot me-2"></i><?= nl2br(e($settings['support_address'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>&copy; <?= date('Y'); ?> <?= e($config['app_name']); ?>. All rights reserved.</span>
            <span>ISO 28000 • TAPA TSR • C-TPAT Certified</span>
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


