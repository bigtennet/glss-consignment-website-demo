<?php

declare(strict_types=1);

return [
'app_name' => 'GLSS Global Logistics Security Company',
    'admin' => [
        'username' => 'admin',
        // Password hash for "changeme123!"
        'password_hash' => '$2y$12$Nww/ZblRVHtA6qDNBII19uGhR75abeIHEwES6Ft3c36basKFQny8O',
    ],
    'database' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'swiftship',
        'username' => 'root',
        'password' => '',
    ],
    'tracking' => [
        'prefix' => 'GLS',
        'length' => 8,
    ],
];


