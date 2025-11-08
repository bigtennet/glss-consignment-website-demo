<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$config = swiftship_config();

$database = new SwiftShip\Database($config['database']);
$pdo = $database->getConnection();

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS shipments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tracking_number VARCHAR(32) NOT NULL UNIQUE,
        sender_name VARCHAR(120) NOT NULL,
        recipient_name VARCHAR(120) NOT NULL,
        origin VARCHAR(160) NOT NULL,
        destination VARCHAR(160) NOT NULL,
        status VARCHAR(60) NOT NULL,
        notes TEXT NULL,
        expected_delivery DATE NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        INDEX idx_status_updated_at (status, updated_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS settings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(120) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$defaultSettings = [
    'site_name' => 'GLSS Global Logistics Security Company',
    'site_tagline' => 'Global Logistics Security Co.',
    'support_email' => 'glss01@europe.com',
    'support_phone' => '+1 (470) 347-2702',
    'support_address' => '1741 Newnan Crossing Blvd, Suite I, Newnan, GA',
    'hero_heading' => 'Global logistics security you can trust',
    'hero_subheading' => 'GLSS delivers escorted, sensor-tracked freight for organizations that cannot compromise on custody or visibility.',
    'mail_from_name' => 'GLSS Command Desk',
    'mail_from_email' => 'glss01@europe.com',
    'mail_use_smtp' => '0',
    'mail_smtp_host' => '',
    'mail_smtp_port' => '587',
    'mail_smtp_username' => '',
    'mail_smtp_password' => '',
    'mail_smtp_encryption' => 'tls',
];

$insertSetting = $pdo->prepare(
    'INSERT INTO settings (setting_key, setting_value, updated_at)
     VALUES (:key, :value, NOW())
     ON DUPLICATE KEY UPDATE setting_value = setting_value'
);

foreach ($defaultSettings as $key => $value) {
    $insertSetting->execute([
        'key' => $key,
        'value' => $value,
    ]);
}

echo "Database migrated successfully.\n";


