<?php

declare(strict_types=1);

namespace SwiftShip;

use DateTimeImmutable;
use PDO;

class Shipments
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM shipments ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByTrackingNumber(string $trackingNumber): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM shipments WHERE tracking_number = :tracking_number LIMIT 1');
        $stmt->execute(['tracking_number' => strtoupper($trackingNumber)]);
        $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $shipment ?: null;
    }

    public function create(array $data): string
    {
        $trackingNumber = $data['tracking_number'] ?? $this->generateTrackingNumber();
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $expectedDelivery = null;

        if (!empty($data['expected_delivery'])) {
            try {
                $expectedDate = new DateTimeImmutable($data['expected_delivery']);
                $expectedDelivery = $expectedDate->format('Y-m-d');
            } catch (\Exception) {
                $expectedDelivery = null;
            }
        }
        $stmt = $this->db->prepare(
            'INSERT INTO shipments (tracking_number, sender_name, recipient_name, origin, destination, status, notes, expected_delivery, created_at, updated_at)
             VALUES (:tracking_number, :sender_name, :recipient_name, :origin, :destination, :status, :notes, :expected_delivery, :created_at, :updated_at)'
        );

        $stmt->execute([
            'tracking_number' => strtoupper($trackingNumber),
            'sender_name' => $data['sender_name'],
            'recipient_name' => $data['recipient_name'],
            'origin' => $data['origin'],
            'destination' => $data['destination'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'expected_delivery' => $expectedDelivery,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $trackingNumber;
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): void
    {
        $stmt = $this->db->prepare(
            'UPDATE shipments SET status = :status, notes = :notes, updated_at = :updated_at WHERE id = :id'
        );

        $stmt->execute([
            'status' => $status,
            'notes' => $notes,
            'updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    private function generateTrackingNumber(): string
    {
        $config = require __DIR__ . '/../config/config.php';
        $prefix = $config['tracking']['prefix'];
        $length = $config['tracking']['length'];

        $attempts = 0;

        do {
            $tracking = $prefix . strtoupper(bin2hex(random_bytes($length / 2)));
            $attempts++;
        } while ($this->findByTrackingNumber($tracking) !== null && $attempts < 5);

        if ($attempts >= 5) {
            throw new \RuntimeException('Unable to generate unique tracking number');
        }

        return $tracking;
    }
}


