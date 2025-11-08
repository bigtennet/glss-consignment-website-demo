<?php

declare(strict_types=1);

if (!function_exists('glss_load_env')) {
    function glss_load_env(?string $path = null): void
    {
        $envPath = $path ?? dirname(__DIR__) . '/.env';

        if (!is_file($envPath) || !is_readable($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            [$key, $value] = $parts;
            $key = trim($key);
            $value = trim($value);

            if ($value !== '' && $value[0] === '"' && substr($value, -1) === '"') {
                $value = substr($value, 1, -1);
            }

            if ($value !== '' && $value[0] === '\'' && substr($value, -1) === '\'') {
                $value = substr($value, 1, -1);
            }

            putenv(sprintf('%s=%s', $key, $value));
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

glss_load_env();
