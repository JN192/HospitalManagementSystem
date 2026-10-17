<?php
require_once __DIR__ . '/../config/database.php';

class Service
{
    public static function getAll(string $search = ''): array
    {
        $pdo = getDBConnection();
        if ($search !== '') {
            $stmt = $pdo->prepare(
                "SELECT * FROM services 
                 WHERE service_name LIKE :s OR category LIKE :s
                 ORDER BY service_id DESC"
            );
            $stmt->execute(['s' => "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM services ORDER BY service_id DESC");
        }
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function nameExists(string $name, int $excludeId = 0): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM services WHERE service_name = :name AND service_id != :id"
        );
        $stmt->execute(['name' => $name, 'id' => $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(array $data): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO services (service_name, category, rate, status)
             VALUES (:service_name, :category, :rate, 'Active')"
        );
        $stmt->execute($data);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = getDBConnection();
        $data['id'] = $id;
        $stmt = $pdo->prepare(
            "UPDATE services SET service_name = :service_name, category = :category, rate = :rate
             WHERE service_id = :id"
        );
        $stmt->execute($data);
    }

    public static function setRate(int $id, string $rate): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("UPDATE services SET rate = :rate WHERE service_id = :id")
            ->execute(['rate' => $rate, 'id' => $id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("UPDATE services SET status = :status WHERE service_id = :id")
            ->execute(['status' => $status, 'id' => $id]);
    }
}