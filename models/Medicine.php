<?php
require_once __DIR__ . '/../config/database.php';

class Medicine
{
    public static function getAll(string $search = ''): array
    {
        $pdo = getDBConnection();
        if ($search !== '') {
            $stmt = $pdo->prepare(
                "SELECT * FROM medicines WHERE medicine_name LIKE :s OR dosage_form LIKE :s ORDER BY medicine_name"
            );
            $stmt->execute(['s' => "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM medicines ORDER BY medicine_name");
        }
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM medicines WHERE medicine_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO medicines (medicine_name, dosage_form, unit_price) VALUES (:medicine_name, :dosage_form, :unit_price)"
        );
        $stmt->execute($data);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = getDBConnection();
        $data['id'] = $id;
        $stmt = $pdo->prepare(
            "UPDATE medicines SET medicine_name = :medicine_name, dosage_form = :dosage_form, unit_price = :unit_price WHERE medicine_id = :id"
        );
        $stmt->execute($data);
    }

    public static function delete(int $id): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("DELETE FROM medicines WHERE medicine_id = :id")->execute(['id' => $id]);
    }
}