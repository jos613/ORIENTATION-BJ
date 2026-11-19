<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateInterval;
use DateTimeImmutable;

final class AdminDashboard
{
    public static function overview(): array
    {
        $db = Database::connection();
        $counts = $db->query(
            "SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'active') AS active_students,
                (SELECT COUNT(*) FROM orientation_tests WHERE status = 'completed') AS completed_tests,
                (SELECT COUNT(*) FROM orientation_tests WHERE status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS tests_last_30_days,
                (SELECT COUNT(*) FROM orientation_tests WHERE status = 'in_progress' AND started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS tests_in_progress,
                (SELECT COUNT(*) FROM programs WHERE is_active = 1) AS active_programs,
                (SELECT COUNT(*) FROM institutions WHERE is_active = 1) AS active_institutions,
                (SELECT COUNT(*) FROM careers WHERE is_active = 1) AS active_careers"
        )->fetch();

        $statement = $db->query(
            "SELECT DATE(completed_at) AS test_day, COUNT(*) AS test_count
             FROM orientation_tests
             WHERE status = 'completed' AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(completed_at) ORDER BY test_day"
        );
        $byDay = [];
        foreach ($statement->fetchAll() as $row) $byDay[(string) $row['test_day']] = (int) $row['test_count'];
        $today = new DateTimeImmutable('today');
        $dayLabels = [1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam', 7 => 'Dim'];
        $week = [];
        for ($offset = 6; $offset >= 0; $offset--) {
            $day = $today->sub(new DateInterval('P' . $offset . 'D'));
            $key = $day->format('Y-m-d');
            $week[] = ['label' => $dayLabels[(int) $day->format('N')], 'date' => $key, 'count' => $byDay[$key] ?? 0];
        }
        $max = max(1, ...array_column($week, 'count'));
        foreach ($week as &$day) $day['height'] = max(4, (int) round($day['count'] / $max * 100));
        unset($day);

        $statement = $db->query(
            "SELECT d.name, COUNT(*) AS tests_count
             FROM test_domain_results r
             JOIN orientation_tests t ON t.id = r.test_id AND t.status = 'completed'
             JOIN domains d ON d.id = r.domain_id AND d.is_active = 1
             WHERE r.rank_position = 1
             GROUP BY d.id, d.name ORDER BY tests_count DESC, d.name ASC LIMIT 5"
        );
        return ['counts' => $counts, 'weekly_tests' => $week, 'top_domains' => $statement->fetchAll()];
    }
}
