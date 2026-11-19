<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class BacSeries
{
    public static function all(): array
    {
        return Database::connection()->query(
            'SELECT s.id, s.code, s.name, s.description, s.is_active, s.created_at,
                    (SELECT COUNT(*) FROM student_profiles p WHERE p.bac_series_id = s.id) AS profile_count
             FROM bac_series s
             ORDER BY s.is_active DESC, s.name ASC'
        )->fetchAll();
    }

    public static function availableForProfile(?int $selectedId): array
    {
        if ($selectedId === null) {
            return Database::connection()->query(
                'SELECT id, code, name, is_active FROM bac_series WHERE is_active = 1 ORDER BY name ASC'
            )->fetchAll();
        }

        $statement = Database::connection()->prepare(
            'SELECT id, code, name, is_active
             FROM bac_series
             WHERE is_active = 1 OR id = :selected_id
             ORDER BY name ASC'
        );
        $statement->execute(['selected_id' => $selectedId]);

        return $statement->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, code, name, description, is_active FROM bac_series WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $series = $statement->fetch();

        return $series ?: null;
    }

    public static function usageCount(int $id): int
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM student_profiles WHERE bac_series_id = :id'
        );
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn();
    }

    public static function create(array $values): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO bac_series (code, name, description, is_active)
             VALUES (:code, :name, :description, :is_active)'
        );
        $statement->execute([
            'code' => $values['code'],
            'name' => $values['name'],
            'description' => $values['description'],
            'is_active' => $values['is_active'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $values): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE bac_series SET code = :code, name = :name, description = :description, is_active = :is_active WHERE id = :id'
        );
        $statement->execute([
            'code' => $values['code'],
            'name' => $values['name'],
            'description' => $values['description'],
            'is_active' => $values['is_active'],
            'id' => $id,
        ]);
    }

    public static function toggle(int $id): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE bac_series SET is_active = IF(is_active = 1, 0, 1) WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    public static function delete(int $id): void
    {
        $statement = Database::connection()->prepare('DELETE FROM bac_series WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
