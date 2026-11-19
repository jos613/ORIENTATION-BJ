<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class StudentProfile
{
    public static function findByUserId(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT p.user_id, p.bac_series_id, p.first_name, p.last_name, p.phone, p.city, p.bac_year,
                    s.code AS bac_series_code, s.name AS bac_series_name, s.is_active AS bac_series_active
             FROM student_profiles p
             LEFT JOIN bac_series s ON s.id = p.bac_series_id
             WHERE p.user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $profile = $statement->fetch();

        return $profile ?: null;
    }

    public static function update(int $userId, array $values): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE student_profiles
             SET first_name = :first_name,
                 last_name = :last_name,
                 bac_series_id = :bac_series_id,
                 bac_year = :bac_year,
                 phone = :phone,
                 city = :city
             WHERE user_id = :user_id'
        );
        $statement->execute([
            'first_name' => $values['first_name'],
            'last_name' => $values['last_name'],
            'bac_series_id' => $values['bac_series_id'],
            'bac_year' => $values['bac_year'],
            'phone' => $values['phone'],
            'city' => $values['city'],
            'user_id' => $userId,
        ]);
    }
}
