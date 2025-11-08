<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$config = swiftship_config();
$brandTokens = preg_split('/\s+/', trim($config['app_name']));
$auth = new SwiftShip\Auth($config['admin']);
$auth->requireAuth();

$adminBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($adminBaseUrl === '' || $adminBaseUrl === '.') {
    $adminBaseUrl = '';
}

$adminUrl = static function (string $path) use ($adminBaseUrl): string {
    $normalized = '/' . ltrim($path, '/');
    return ($adminBaseUrl === '' ? '' : $adminBaseUrl) . $normalized;
};

$database = new SwiftShip\Database($config['database']);
$pdo = $database->getConnection();
$shipmentsRepo = new SwiftShip\Shipments($pdo);
$settingsRepo = new SwiftShip\Settings($pdo);

$settings = $settingsRepo->all();
$mailer = new SwiftShip\Mailer($settings);

$statuses = [
    'Label Created',
    'Processing',
    'In Transit',
    'Arrived at Hub',
    'Out for Delivery',
    'Delivered',
    'On Hold',
    'Exception',
    'Cancelled',
];

$flash = [
    'type' => null,
    'message' => null,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $required = ['sender_name', 'recipient_name', 'origin', 'destination', 'status'];
        $missing = array_filter($required, fn($field) => empty($_POST[$field]));

        if ($missing) {
            $flash = [
                'type' => 'danger',
                'message' => 'Please fill in all required fields to create a shipment.',
            ];
        } else {
            try {
                $trackingNumber = $shipmentsRepo->create([
                    'sender_name' => trim((string) $_POST['sender_name']),
                    'recipient_name' => trim((string) $_POST['recipient_name']),
                    'origin' => trim((string) $_POST['origin']),
                    'destination' => trim((string) $_POST['destination']),
                    'status' => in_array($_POST['status'], $statuses, true) ? $_POST['status'] : $statuses[0],
                    'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
                    'expected_delivery' => $_POST['expected_delivery'] ?: null,
                ]);

                $flash = [
                    'type' => 'success',
                    'message' => sprintf('Shipment created successfully. Tracking number: %s', htmlspecialchars($trackingNumber, ENT_QUOTES, 'UTF-8')),
                ];
            } catch (Throwable $throwable) {
                $flash = [
                    'type' => 'danger',
                    'message' => 'Unable to save shipment: ' . $throwable->getMessage(),
                ];
            }
        }
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $notes = trim((string) ($_POST['notes'] ?? ''));

        if ($id <= 0 || !in_array($status, $statuses, true)) {
            $flash = [
                'type' => 'danger',
                'message' => 'Invalid shipment data.',
            ];
        } else {
            try {
                $shipmentsRepo->updateStatus($id, $status, $notes ?: null);
                $flash = [
                    'type' => 'success',
                    'message' => 'Shipment updated successfully.',
                ];
            } catch (Throwable $throwable) {
                $flash = [
                    'type' => 'danger',
                    'message' => 'Unable to update shipment: ' . $throwable->getMessage(),
                ];
            }
        }
    }
    if ($action === 'settings') {
        $siteName = trim((string) ($_POST['site_name'] ?? ''));
        $siteTagline = trim((string) ($_POST['site_tagline'] ?? ''));
        $supportEmail = trim((string) ($_POST['support_email'] ?? ''));
        $supportPhone = trim((string) ($_POST['support_phone'] ?? ''));
        $supportAddress = trim((string) ($_POST['support_address'] ?? ''));
        $heroHeading = trim((string) ($_POST['hero_heading'] ?? ''));
        $heroSubheading = trim((string) ($_POST['hero_subheading'] ?? ''));
        $mailFromName = trim((string) ($_POST['mail_from_name'] ?? ''));
        $mailFromEmail = trim((string) ($_POST['mail_from_email'] ?? ''));
        $mailUseSmtp = isset($_POST['mail_use_smtp']) ? '1' : '0';
        $mailSmtpHost = trim((string) ($_POST['mail_smtp_host'] ?? ''));
        $mailSmtpPort = trim((string) ($_POST['mail_smtp_port'] ?? '587'));
        $mailSmtpUsername = trim((string) ($_POST['mail_smtp_username'] ?? ''));
        $mailSmtpPasswordInput = $_POST['mail_smtp_password'] ?? '';
        $mailSmtpEncryption = trim((string) ($_POST['mail_smtp_encryption'] ?? 'tls'));

        if ($mailSmtpPasswordInput === '') {
            $mailSmtpPassword = $settings['mail_smtp_password'] ?? '';
        } else {
            $mailSmtpPassword = (string) $mailSmtpPasswordInput;
        }

        if ($siteName === '' || !filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
            $flash = [
                'type' => 'danger',
                'message' => 'Site name and a valid support email are required.',
            ];
        } elseif ($mailFromEmail !== '' && !filter_var($mailFromEmail, FILTER_VALIDATE_EMAIL)) {
            $flash = [
                'type' => 'danger',
                'message' => 'Please provide a valid sender email address.',
            ];
        } else {
            try {
                $settingsRepo->updateMany([
                    'site_name' => $siteName,
                    'site_tagline' => $siteTagline,
                    'support_email' => $supportEmail,
                    'support_phone' => $supportPhone,
                    'support_address' => $supportAddress,
                    'hero_heading' => $heroHeading,
                    'hero_subheading' => $heroSubheading,
                    'mail_from_name' => $mailFromName !== '' ? $mailFromName : $siteName,
                    'mail_from_email' => $mailFromEmail !== '' ? $mailFromEmail : $supportEmail,
                    'mail_use_smtp' => $mailUseSmtp,
                    'mail_smtp_host' => $mailSmtpHost,
                    'mail_smtp_port' => $mailSmtpPort !== '' ? $mailSmtpPort : '587',
                    'mail_smtp_username' => $mailSmtpUsername,
                    'mail_smtp_password' => $mailSmtpPassword,
                    'mail_smtp_encryption' => $mailSmtpEncryption !== '' ? $mailSmtpEncryption : 'tls',
                ]);

                $settings = $settingsRepo->all();
                $mailer = new SwiftShip\Mailer($settings);

                $flash = [
                    'type' => 'success',
                    'message' => 'Site settings updated successfully.',
                ];
            } catch (Throwable $throwable) {
                $flash = [
                    'type' => 'danger',
                    'message' => 'Unable to update settings: ' . $throwable->getMessage(),
                ];
            }
        }
    }
    if ($action === 'send_mail') {
        $mailTo = trim((string) ($_POST['mail_to'] ?? ''));
        $mailSubject = trim((string) ($_POST['mail_subject'] ?? ''));
        $mailMessage = trim((string) ($_POST['mail_message'] ?? ''));

        if (!filter_var($mailTo, FILTER_VALIDATE_EMAIL) || $mailSubject === '' || $mailMessage === '') {
            $flash = [
                'type' => 'danger',
                'message' => 'Recipient email, subject, and message are all required to send email.',
            ];
        } else {
            $sent = $mailer->send($mailTo, $mailSubject, $mailMessage);

            $flash = [
                'type' => $sent ? 'success' : 'danger',
                'message' => $sent
                    ? 'Email dispatched successfully.'
                    : 'Unable to send email. Please verify your mail settings or server configuration.',
            ];
        }
    }
}

$shipments = $shipmentsRepo->all();

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
    <title>Dashboard | <?= e($config['app_name']); ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: #f3f5fb;
            min-height: 100vh;
        }

        .layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: #111a24;
            color: #ecf2ff;
            display: flex;
            flex-direction: column;
            padding: 1.75rem 1.5rem;
        }

        .sidebar .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
            text-decoration: none;
            color: inherit;
        }

        .sidebar .brand i {
            font-size: 1.6rem;
            color: #ffcc00;
        }

        .sidebar .brand span {
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(236, 242, 255, 0.8);
            text-decoration: none;
            padding: 0.65rem 0.5rem;
            border-radius: 0.75rem;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .sidebar nav a:hover,
        .sidebar nav a.active {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .topbar {
            background: #ffffff;
            border-bottom: 1px solid rgba(17, 26, 36, 0.08);
            padding: 1rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .main-content {
            padding: 2rem 2.5rem;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 18px 32px rgba(17, 26, 36, 0.08);
        }

        .card-header {
            border-bottom: none;
            background: transparent;
        }

        .stats-small {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stats-small .card {
            box-shadow: none;
            background: #fff;
            border: 1px solid rgba(17, 26, 36, 0.05);
        }

        .badge-status {
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
        }

        @media (max-width: 992px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <a class="brand" href="<?= $adminUrl('dashboard.php'); ?>">
            <i class="fa-solid fa-shield"></i>
            <span><?= e($brandTokens[0] ?? 'GLSS'); ?> Admin</span>
        </a>
        <nav class="d-flex flex-column gap-1">
            <a href="#settings" class="active"><i class="fa-solid fa-sliders"></i> Site settings</a>
            <a href="#shipments"><i class="fa-solid fa-box"></i> Shipments</a>
            <a href="#mail"><i class="fa-solid fa-envelope"></i> Mail desk</a>
        </nav>
        <div class="mt-auto pt-4 border-top border-secondary">
            <small class="text-uppercase text-muted">Signed in</small>
            <div class="fw-semibold">Administrator</div>
            <a class="btn btn-outline-light btn-sm mt-3 w-100" href="<?= $adminUrl('logout.php'); ?>">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign out
            </a>
        </div>
    </aside>

    <div class="content-wrapper">
        <header class="topbar">
            <div>
                <h1 class="h4 fw-bold mb-0">Operations dashboard</h1>
                <small class="text-muted">Monitor consignments and mission configuration</small>
            </div>
            <div class="d-none d-lg-flex align-items-center gap-3">
                <span class="text-muted small"><i class="fa-solid fa-location-dot me-2"></i><?= e($settings['support_address'] ?? ''); ?></span>
                <span class="badge text-bg-primary">Secure</span>
        </div>
        </header>

        <main class="main-content">
        <?php if ($flash['message']): ?>
            <div class="alert alert-<?= e($flash['type'] ?? 'info'); ?>"><?= $flash['message']; ?></div>
        <?php endif; ?>

            <section id="settings" class="mb-4">
        <div class="row g-4">
                    <div class="col-xl-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h2 class="h5 fw-bold mb-3">Site settings</h2>
                                <form method="post" class="row gy-3">
                                    <input type="hidden" name="action" value="settings">
                                    <div class="col-12">
                                        <h3 class="h6 text-uppercase text-muted mb-2">Branding & contact</h3>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Site name *</label>
                                        <input type="text" class="form-control" name="site_name" value="<?= e($settings['site_name'] ?? $config['app_name']); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Site tagline</label>
                                        <input type="text" class="form-control" name="site_tagline" value="<?= e($settings['site_tagline'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Support email *</label>
                                        <input type="email" class="form-control" name="support_email" value="<?= e($settings['support_email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Support phone</label>
                                        <input type="text" class="form-control" name="support_phone" value="<?= e($settings['support_phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Support address</label>
                                        <textarea class="form-control" name="support_address" rows="2"><?= e($settings['support_address'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Hero heading</label>
                                        <input type="text" class="form-control" name="hero_heading" value="<?= e($settings['hero_heading'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Hero subheading</label>
                                        <textarea class="form-control" name="hero_subheading" rows="2"><?= e($settings['hero_subheading'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                    <div class="col-12">
                                        <h3 class="h6 text-uppercase text-muted mb-2">Mail configuration</h3>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Sender name</label>
                                        <input type="text" class="form-control" name="mail_from_name" value="<?= e($settings['mail_from_name'] ?? ($settings['site_name'] ?? $config['app_name'])); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Sender email *</label>
                                        <input type="email" class="form-control" name="mail_from_email" value="<?= e($settings['mail_from_email'] ?? ($settings['support_email'] ?? '')); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="mail_use_smtp" id="mail_use_smtp" value="1" <?= ($settings['mail_use_smtp'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="mail_use_smtp">
                                                Send mail through SMTP
                                            </label>
                                        </div>
                                        <small class="text-muted">Leave unchecked to use the server's default mail configuration.</small>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold">SMTP host</label>
                                        <input type="text" class="form-control" name="mail_smtp_host" value="<?= e($settings['mail_smtp_host'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">SMTP port</label>
                                        <input type="text" class="form-control" name="mail_smtp_port" value="<?= e($settings['mail_smtp_port'] ?? '587'); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Encryption</label>
                                        <select class="form-select" name="mail_smtp_encryption">
                                            <?php
                                            $encryption = $settings['mail_smtp_encryption'] ?? 'tls';
                                            foreach (['none' => 'None', 'ssl' => 'SSL', 'tls' => 'TLS'] as $value => $label): ?>
                                                <option value="<?= e($value); ?>" <?= $value === $encryption ? 'selected' : ''; ?>><?= e($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">SMTP username</label>
                                        <input type="text" class="form-control" name="mail_smtp_username" value="<?= e($settings['mail_smtp_username'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">SMTP password</label>
                                        <input type="password" class="form-control" name="mail_smtp_password" placeholder="Leave blank to keep current password">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-dark w-100">Save settings</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card shadow-sm border-0" id="mail">
                            <div class="card-body">
                                <h2 class="h6 fw-bold mb-3">Send operations email</h2>
                                <p class="text-muted small">
                                    Use this form to send a quick message using the configuration saved above.
                                </p>
                                <form method="post" class="row gy-3">
                                    <input type="hidden" name="action" value="send_mail">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Recipient email *</label>
                                        <input type="email" class="form-control" name="mail_to" value="<?= e($settings['support_email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Subject *</label>
                                        <input type="text" class="form-control" name="mail_subject" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Message *</label>
                                        <textarea class="form-control" name="mail_message" rows="4" required placeholder="Write your message here..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-outline-dark w-100">Send email</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-8">
                        <div class="row g-3 stats-small" id="shipments">
                            <div class="col-md-4">
                                <div class="card p-3">
                                    <span class="text-uppercase text-muted small"><i class="fa-solid fa-box me-2"></i>Total consignments</span>
                                    <h3 class="h4 fw-bold mb-0"><?= count($shipments); ?></h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card p-3">
                                    <span class="text-uppercase text-muted small"><i class="fa-solid fa-truck-fast me-2"></i>In transit</span>
                                    <h3 class="h4 fw-bold mb-0"><?= count(array_filter($shipments, fn($s) => $s['status'] === 'In Transit')); ?></h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card p-3">
                                    <span class="text-uppercase text-muted small"><i class="fa-solid fa-clock me-2"></i>Awaiting update</span>
                                    <h3 class="h4 fw-bold mb-0"><?= count(array_filter($shipments, fn($s) => $s['status'] !== 'Delivered')); ?></h3>
                                </div>
                            </div>
                        </div>
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                    <div>
                                        <h2 class="h5 fw-bold mb-1">Create new shipment</h2>
                                        <p class="text-muted small mb-0">Generate a secured GLS tracking reference for your next consignment.</p>
                                    </div>
                                </div>
                        <form method="post" class="row gy-3">
                            <input type="hidden" name="action" value="create">
                                    <div class="col-md-6">
                                <label class="form-label fw-semibold">Sender name *</label>
                                <input type="text" class="form-control" name="sender_name" required>
                            </div>
                                    <div class="col-md-6">
                                <label class="form-label fw-semibold">Recipient name *</label>
                                <input type="text" class="form-control" name="recipient_name" required>
                            </div>
                                    <div class="col-md-6">
                                <label class="form-label fw-semibold">Origin *</label>
                                <input type="text" class="form-control" name="origin" placeholder="City, Country" required>
                            </div>
                                    <div class="col-md-6">
                                <label class="form-label fw-semibold">Destination *</label>
                                <input type="text" class="form-control" name="destination" placeholder="City, Country" required>
                            </div>
                                    <div class="col-md-6">
                                <label class="form-label fw-semibold">Status *</label>
                                <select class="form-select" name="status" required>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?= e($status); ?>"><?= e($status); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                                    <div class="col-md-6">
                                <label class="form-label fw-semibold">Expected delivery</label>
                                <input type="date" class="form-control" name="expected_delivery">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Optional delivery instructions or notes"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Create shipment</button>
                            </div>
                        </form>
                    </div>
                </div>
                        <div class="card shadow-sm border-0 mt-4">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                    <div>
                                        <h2 class="h5 fw-bold mb-1">Recent consignments</h2>
                                        <p class="text-muted small mb-0">Track and update live movements in real time.</p>
            </div>
                                    <span class="badge text-bg-primary mt-2 mt-md-0"><?= count($shipments); ?> total</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Tracking</th>
                                        <th>Recipient</th>
                                        <th>Route</th>
                                        <th>Status</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$shipments): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No consignments yet. Create one to get started.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($shipments as $shipment): ?>
                                            <tr>
                                                <td class="fw-semibold"><?= e($shipment['tracking_number']); ?></td>
                                                <td>
                                                    <div><?= e($shipment['recipient_name']); ?></div>
                                                    <small class="text-muted">From <?= e($shipment['sender_name']); ?></small>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?= e($shipment['origin']); ?></small>
                                                    <div class="fw-semibold">→ <?= e($shipment['destination']); ?></div>
                                                </td>
                                                <td>
                                                        <span class="badge text-bg-secondary badge-status"><?= e($shipment['status']); ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?= e(date('M j, Y g:i A', strtotime($shipment['updated_at']))); ?></small>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit-<?= (int) $shipment['id']; ?>">
                                                        Update
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="collapse" id="edit-<?= (int) $shipment['id']; ?>">
                                                <td colspan="6">
                                                    <form method="post" class="row gy-2">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="id" value="<?= (int) $shipment['id']; ?>">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold">Status</label>
                                                            <select class="form-select form-select-sm" name="status" required>
                                                                <?php foreach ($statuses as $status): ?>
                                                                    <option value="<?= e($status); ?>" <?= $status === $shipment['status'] ? 'selected' : ''; ?>>
                                                                        <?= e($status); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Notes</label>
                                                            <textarea class="form-control form-control-sm" name="notes" rows="2"><?= e($shipment['notes'] ?? ''); ?></textarea>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="submit" class="btn btn-primary btn-sm w-100">Save</button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-0">Tracking numbers are generated automatically. Share them with customers for real-time status updates.</p>
                    </div>
                </div>
            </div>
        </div>
            </section>
    </main>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

