-- GLSS logistics database schema

CREATE TABLE IF NOT EXISTS `shipments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tracking_number` VARCHAR(32) NOT NULL UNIQUE,
    `sender_name` VARCHAR(120) NOT NULL,
    `recipient_name` VARCHAR(120) NOT NULL,
    `origin` VARCHAR(160) NOT NULL,
    `destination` VARCHAR(160) NOT NULL,
    `status` VARCHAR(60) NOT NULL,
    `notes` TEXT NULL,
    `expected_delivery` DATE NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_status_updated_at` (`status`, `updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(120) NOT NULL UNIQUE,
    `setting_value` TEXT NOT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
