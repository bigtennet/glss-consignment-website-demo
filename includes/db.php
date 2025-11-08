<?php

declare(strict_types=1);

namespace SwiftShip;

use PDO;
use PDOException;

class Database
{
    private PDO $connection;

    public function __construct(array $config)
    {
        $this->connection = $this->createConnection($config);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function createConnection(array $config): PDO
    {
        $required = ['host', 'port', 'name', 'username', 'password'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $config)) {
                throw new PDOException(sprintf('Missing database configuration value for "%s"', $key));
            }
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=utf8mb4',
            $config['host'],
            (int) $config['port']
        );

        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);

        $databaseName = $config['name'];
        $pdo->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $databaseName));
        $pdo->exec(sprintf('USE `%s`', $databaseName));

        return $pdo;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}


