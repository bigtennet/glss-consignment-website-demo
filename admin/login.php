<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$config = swiftship_config();
$auth = new SwiftShip\Auth($config['admin']);

$adminBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($adminBaseUrl === '' || $adminBaseUrl === '.') {
    $adminBaseUrl = '';
}

$adminUrl = static function (string $path) use ($adminBaseUrl): string {
    $normalized = '/' . ltrim($path, '/');
    return ($adminBaseUrl === '' ? '' : $adminBaseUrl) . $normalized;
};

if ($auth->check()) {
    header('Location: ' . $adminUrl('dashboard.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($auth->attempt($username, $password)) {
        header('Location: ' . $adminUrl('dashboard.php'));
        exit;
    }

    $error = 'Invalid credentials. Please try again.';
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
    <title>Admin login | <?= e($config['app_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <h1 class="h3 mb-3 text-center fw-bold"><?= e($config['app_name']); ?> Admin</h1>
                        <p class="text-muted text-center mb-4">Sign in to manage consignments.</p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= e($error); ?></div>
                        <?php endif; ?>

                        <form method="post" class="row gy-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control" required autofocus>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100" type="submit">Sign in</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="/" class="text-decoration-none text-muted">← Back to site</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

