<?php

declare(strict_types=1);

namespace SwiftShip;

use PDO;

class Settings
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT setting_key, setting_value FROM settings');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            return $default;
        }

        return $value;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO settings (setting_key, setting_value, updated_at)
             VALUES (:key, :value, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
        );

        $stmt->execute([
            'key' => $key,
            'value' => $value,
        ]);
    }

    public function updateMany(array $data): void
    {
        $this->db->beginTransaction();

        try {
            foreach ($data as $key => $value) {
                $this->set($key, $value);
            }

            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            throw $throwable;
        }
    }
}


