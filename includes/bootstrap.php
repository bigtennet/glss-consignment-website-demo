<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/shipments.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/mailer.php';

function swiftship_config(): array
{
    static $config;

    if ($config === null) {
        $configFile = require __DIR__ . '/../config/config.php';

        $config = [
            'app_name' => env('GLSS_APP_NAME', $configFile['app_name'] ?? 'GLSS'),
            'admin' => [
                'username' => env('GLSS_ADMIN_USER', $configFile['admin']['username'] ?? 'admin'),
                'password_hash' => env('GLSS_ADMIN_PASSWORD_HASH', $configFile['admin']['password_hash'] ?? ''),
            ],
            'database' => [
                'driver' => env('GLSS_DB_DRIVER', $configFile['database']['driver'] ?? 'mysql'),
                'host' => env('GLSS_DB_HOST', $configFile['database']['host'] ?? '127.0.0.1'),
                'port' => (int) env('GLSS_DB_PORT', (string) ($configFile['database']['port'] ?? 3306)),
                'name' => env('GLSS_DB_NAME', $configFile['database']['name'] ?? 'glss'),
                'username' => env('GLSS_DB_USER', $configFile['database']['username'] ?? 'root'),
                'password' => env('GLSS_DB_PASSWORD', $configFile['database']['password'] ?? ''),
            ],
            'tracking' => [
                'prefix' => env('GLSS_TRACKING_PREFIX', $configFile['tracking']['prefix'] ?? 'GLS'),
                'length' => (int) env('GLSS_TRACKING_LENGTH', (string) ($configFile['tracking']['length'] ?? 8)),
            ],
        ];

        try {
            $database = new \SwiftShip\Database($config['database']);
            $settingsRepo = new \SwiftShip\Settings($database->getConnection());
            $settings = $settingsRepo->all();

            if (!empty($settings['site_name'])) {
                $config['app_name'] = $settings['site_name'];
            }

            $config['settings'] = $settings;
        } catch (\Throwable $throwable) {
            $config['settings_error'] = $throwable->getMessage();
        }
    }

    return $config;
}


