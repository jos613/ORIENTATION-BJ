<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class TestHistory
{
    public static function forUser(int $userId): array
    {
        $db = Database::connection();
        $expire = $db->prepare(
            "UPDATE orientation_tests SET status = 'abandoned'
             WHERE user_id = ? AND status = 'in_progress'
               AND started_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $expire->execute([$userId]);
        $statement = $db->prepare(
            'SELECT t.id, t.status, t.started_at, t.completed_at, t.question_count,
                    (SELECT COUNT(DISTINCT a.question_id) FROM test_answers a WHERE a.test_id = t.id) AS answered_count,
                    (SELECT d.name FROM test_domain_results r JOIN domains d ON d.id = r.domain_id
                     WHERE r.test_id = t.id ORDER BY r.rank_position ASC LIMIT 1) AS top_domain_name,
                    (SELECT r.percentage FROM test_domain_results r
                     WHERE r.test_id = t.id ORDER BY r.rank_position ASC LIMIT 1) AS top_domain_percentage
             FROM orientation_tests t
             WHERE t.user_id = :user_id
             ORDER BY COALESCE(t.completed_at, t.started_at) DESC, t.id DESC'
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }
}
