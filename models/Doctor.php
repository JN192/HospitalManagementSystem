<?php
require_once __DIR__ . '/../config/database.php';

class Doctor
{
    public static function getAll(string $search = ''): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT dr.*, dp.department_name 
                FROM doctors dr 
                JOIN departments dp ON dr.department_id = dp.department_id";
        if ($search !== '') {
            $sql .= " WHERE dr.doctor_name LIKE :s OR dr.doctor_code LIKE :s OR dr.specialization LIKE :s";
            $sql .= " ORDER BY dr.doctor_id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['s' => "%$search%"]);
        } else {
            $sql .= " ORDER BY dr.doctor_id DESC";
            $stmt = $pdo->query($sql);
        }
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE doctor_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getAllDepartments(): array
    {
        $pdo = getDBConnection();
        return $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();
    }

    public static function create(array $data): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO doctors (doctor_code, doctor_name, specialization, department_id, contact_number, status)
             VALUES (:doctor_code, :doctor_name, :specialization, :department_id, :contact_number, 'Active')"
        );
        $stmt->execute($data);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = getDBConnection();
        $data['id'] = $id;
        $stmt = $pdo->prepare(
            "UPDATE doctors SET 
                doctor_code = :doctor_code, doctor_name = :doctor_name, 
                specialization = :specialization, department_id = :department_id,
                contact_number = :contact_number
             WHERE doctor_id = :id"
        );
        $stmt->execute($data);
    }

    public static function deactivate(int $id): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("UPDATE doctors SET status = 'Inactive' WHERE doctor_id = :id")->execute(['id' => $id]);
    }

    public static function activate(int $id): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("UPDATE doctors SET status = 'Active' WHERE doctor_id = :id")->execute(['id' => $id]);
    }

    public static function getActiveDoctors(): array
    {
        $pdo = getDBConnection();
        return $pdo->query(
            "SELECT dr.*, dp.department_name 
             FROM doctors dr JOIN departments dp ON dr.department_id = dp.department_id
             WHERE dr.status = 'Active' ORDER BY dr.doctor_name"
        )->fetchAll();
    }
}