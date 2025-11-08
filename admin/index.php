<?php

$adminBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($adminBaseUrl === '' || $adminBaseUrl === '.') {
    $adminBaseUrl = '';
}

$loginUrl = ($adminBaseUrl === '' ? '' : $adminBaseUrl) . '/login.php';

header('Location: ' . $loginUrl);
exit;
