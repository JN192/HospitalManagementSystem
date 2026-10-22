<?php
require_once __DIR__ . '/../config/database.php';

class Course
{
    public static function getAll(string $search = ''): array
    {
        $pdo = getDBConnection();
        $base = "SELECT c.*, (SELECT COUNT(*) FROM patients p WHERE p.course_id = c.course_id) AS patient_count
                 FROM courses c";
        if ($search !== '') {
            $stmt = $pdo->prepare($base . " WHERE c.course_code LIKE :s1 OR c.course_name LIKE :s2 ORDER BY c.course_code");
            $like = "%$search%";
            $stmt->execute(['s1' => $like, 's2' => $like]);
        } else {
            $stmt = $pdo->query($base . " ORDER BY c.course_code");
        }
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function exists(string $code, string $name, int $excludeId = 0): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM courses WHERE (course_code = :code OR course_name = :name) AND course_id != :id"
        );
        $stmt->execute(['code' => $code, 'name' => $name, 'id' => $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(array $data): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("INSERT INTO courses (course_code, course_name) VALUES (:course_code, :course_name)")
            ->execute($data);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = getDBConnection();
        $data['id'] = $id;
        $pdo->prepare("UPDATE courses SET course_code = :course_code, course_name = :course_name WHERE course_id = :id")
            ->execute($data);
    }

    public static function patientCount(int $id): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE course_id = :id");
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public static function delete(int $id): void
    {
        $pdo = getDBConnection();
        $pdo->prepare("DELETE FROM courses WHERE course_id = :id")->execute(['id' => $id]);
    }
}