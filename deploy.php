<?php

declare(strict_types=1);

$sourceDir = __DIR__;
$targetDir = '/opt/lampp/htdocs/shipping-website';

if (posix_geteuid() !== 0) {
    fwrite(STDERR, "This script needs to run with sudo so it can write to {$targetDir}.\n");
    exit(1);
}

echo "Preparing to deploy SwiftShip to {$targetDir}\n";

require_once __DIR__ . '/includes/bootstrap.php';
$config = swiftship_config();

echo "Running database migrations...\n";
require __DIR__ . '/database/migrate.php';

echo "Copying files...\n";
removeDirectory($targetDir);
copyDirectory($sourceDir, $targetDir);

echo "Deployment complete.\n";

/**
 * Recursively delete a directory if it exists.
 */
function removeDirectory(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }

    rmdir($dir);
}

/**
 * Recursively copy a directory, excluding version-control files.
 */
function copyDirectory(string $source, string $destination): void
{
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $directoryIterator = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

    $excluded = [
        '.git',
        '.gitignore',
        'node_modules',
        'vendor',
        '.idea',
    ];

    foreach ($iterator as $item) {
        $relativePath = substr($item->getPathname(), strlen($source) + 1);

        foreach ($excluded as $exclusion) {
            if (str_starts_with($relativePath, $exclusion)) {
                continue 2;
            }
        }

        $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

        if ($item->isDir()) {
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
        } else {
            copy($item->getPathname(), $targetPath);
        }
    }
}


