<?php
require_once __DIR__ . '/../config/database.php';

class Room
{
    public static function getAll(string $search = ''): array
    {
        $pdo = getDBConnection();
        if ($search !== '') {
            $stmt = $pdo->prepare(
                "SELECT * FROM rooms 
                 WHERE room_number LIKE :s OR room_type LIKE :s OR bed_number LIKE :s
                 ORDER BY room_id DESC"
            );
            $stmt->execute(['s' => "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_id DESC");
        }
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO rooms (room_number, room_type, bed_number, room_rate, status)
             VALUES (:room_number, :room_type, :bed_number, :room_rate, 'Available')"
        );
        $stmt->execute($data);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = getDBConnection();
        $data['id'] = $id;
        $stmt = $pdo->prepare(
            "UPDATE rooms SET 
                room_number = :room_number, room_type = :room_type, 
                bed_number = :bed_number, room_rate = :room_rate
             WHERE room_id = :id"
        );
        $stmt->execute($data);
    }

    public static function setStatus(int $id, string $status): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("UPDATE rooms SET status = :status WHERE room_id = :id")
            ->execute(['status' => $status, 'id' => $id]);
    }

    public static function getAvailableRooms(): array
    {
        $pdo = getDBConnection();
        return $pdo->query("SELECT * FROM rooms WHERE status = 'Available' ORDER BY room_number")->fetchAll();
    }
}