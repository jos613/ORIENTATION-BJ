<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DomainException;

final class Favorites
{
    public static function forUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT d.id AS domain_id, d.name, d.description, f.created_at, t.completed_at,
                    r.percentage, r.rank_position
             FROM favorites f
             JOIN domains d ON d.id = f.domain_id AND d.is_active = 1
             LEFT JOIN orientation_tests t ON t.id = f.test_id
             LEFT JOIN test_domain_results r ON r.test_id = f.test_id AND r.domain_id = d.id
             WHERE f.user_id = :user_id
             ORDER BY f.created_at DESC, d.name ASC'
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }

    public static function domainIdsForUser(int $userId): array
    {
        $statement = Database::connection()->prepare('SELECT domain_id FROM favorites WHERE user_id = ?');
        $statement->execute([$userId]);
        return array_map('intval', $statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    public static function add(int $userId, int $domainId, int $testId): void
    {
        $statement = Database::connection()->prepare(
            "SELECT 1 FROM orientation_tests t
             JOIN test_domain_results r ON r.test_id = t.id AND r.domain_id = :domain_id
             JOIN domains d ON d.id = r.domain_id AND d.is_active = 1
             WHERE t.id = :test_id AND t.user_id = :user_id AND t.status = 'completed'
             LIMIT 1"
        );
        $statement->execute(['domain_id' => $domainId, 'test_id' => $testId, 'user_id' => $userId]);
        if (!$statement->fetchColumn()) {
            throw new DomainException('Ce domaine ne fait pas partie de tes résultats enregistrés.');
        }

        $statement = Database::connection()->prepare(
            'INSERT INTO favorites (user_id, domain_id, test_id)
             VALUES (:user_id, :domain_id, :test_id)
             ON DUPLICATE KEY UPDATE test_id = VALUES(test_id)'
        );
        $statement->execute(['user_id' => $userId, 'domain_id' => $domainId, 'test_id' => $testId]);
    }

    public static function remove(int $userId, int $domainId): void
    {
        $statement = Database::connection()->prepare('DELETE FROM favorites WHERE user_id = ? AND domain_id = ?');
        $statement->execute([$userId, $domainId]);
    }
}
