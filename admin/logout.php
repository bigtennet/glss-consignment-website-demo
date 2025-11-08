<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$config = swiftship_config();
$auth = new SwiftShip\Auth($config['admin']);
$auth->logout();

$adminBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($adminBaseUrl === '' || $adminBaseUrl === '.') {
    $adminBaseUrl = '';
}

$loginUrl = ($adminBaseUrl === '' ? '' : $adminBaseUrl) . '/login.php';

header('Location: ' . $loginUrl);
exit;


