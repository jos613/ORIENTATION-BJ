<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Recommendations
{
    public static function latestForUser(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT id FROM orientation_tests
             WHERE user_id = ? AND status = 'completed'
             ORDER BY completed_at DESC, id DESC LIMIT 1"
        );
        $statement->execute([$userId]);
        $testId = $statement->fetchColumn();
        return $testId ? self::forTest($userId, (int) $testId) : null;
    }

    public static function forTest(int $userId, int $testId): ?array
    {
        $db = Database::connection();
        $statement = $db->prepare(
            "SELECT id, completed_at, question_count FROM orientation_tests
             WHERE id = ? AND user_id = ? AND status = 'completed' LIMIT 1"
        );
        $statement->execute([$testId, $userId]);
        $test = $statement->fetch();
        if (!$test) return null;

        $statement = $db->prepare(
            'SELECT r.domain_id, d.name, d.description, r.score, r.percentage,
                    r.rank_position, r.is_recommended
             FROM test_domain_results r JOIN domains d ON d.id = r.domain_id
             WHERE r.test_id = ? AND d.is_active = 1
             ORDER BY r.percentage DESC, r.score DESC, d.name ASC'
        );
        $statement->execute([$testId]);
        $domains = $statement->fetchAll();
        $recommended = array_values(array_filter($domains, static fn(array $domain): bool => (bool) $domain['is_recommended']));
        $fallback = $recommended === [];
        $domains = array_slice($fallback ? array_slice($domains, 0, 3) : $recommended, 0, 5);

        $statement = $db->prepare(
            'SELECT p.bac_series_id, bs.code AS bac_series_code
             FROM student_profiles p LEFT JOIN bac_series bs ON bs.id = p.bac_series_id
             WHERE p.user_id = ?'
        );
        $statement->execute([$userId]);
        $profile = $statement->fetch() ?: ['bac_series_id' => null, 'bac_series_code' => null];
        $seriesId = $profile['bac_series_id'] === null ? null : (int) $profile['bac_series_id'];
        $favoriteIds = Favorites::domainIdsForUser($userId);

        foreach ($domains as &$domain) {
            $domainId = (int) $domain['domain_id'];
            $domain['is_favorite'] = in_array($domainId, $favoriteIds, true);

            if ($seriesId !== null) {
                $programs = $db->prepare(
                    'SELECT p.id, p.name, p.description, p.admission_requirements, p.duration, p.degree_level
                     FROM programs p JOIN program_bac_series pbs ON pbs.program_id = p.id
                     WHERE p.domain_id = ? AND p.is_active = 1 AND pbs.bac_series_id = ?
                     ORDER BY p.name'
                );
                $programs->execute([$domainId, $seriesId]);
            } else {
                $programs = $db->prepare(
                    'SELECT id, name, description, admission_requirements, duration, degree_level
                     FROM programs WHERE domain_id = ? AND is_active = 1 ORDER BY name'
                );
                $programs->execute([$domainId]);
            }
            $domain['programs'] = $programs->fetchAll();

            foreach ($domain['programs'] as &$program) {
                $query = $db->prepare(
                    'SELECT i.id, i.name, i.institution_type, i.address, i.city, i.department,
                            i.website, i.phone, pi.availability_notes
                     FROM program_institutions pi JOIN institutions i ON i.id = pi.institution_id
                     WHERE pi.program_id = ? AND i.is_active = 1 ORDER BY i.name'
                );
                $query->execute([(int) $program['id']]);
                $program['institutions'] = $query->fetchAll();

                $query = $db->prepare(
                    'SELECT bs.code, bs.name, pbs.series_precision, pbs.ranking_subjects
                     FROM program_bac_series pbs JOIN bac_series bs ON bs.id = pbs.bac_series_id
                     WHERE pbs.program_id = ? ORDER BY bs.code'
                );
                $query->execute([(int) $program['id']]);
                $program['bac_series'] = $query->fetchAll();
            }
            unset($program);

            if ($seriesId !== null) {
                $careers = $db->prepare(
                    'SELECT c.id, c.name, c.description, c.opportunities
                     FROM careers c JOIN program_careers pc ON pc.career_id = c.id
                     JOIN programs p ON p.id = pc.program_id
                     JOIN program_bac_series pbs ON pbs.program_id = p.id
                     WHERE c.domain_id = ? AND c.is_active = 1 AND p.is_active = 1
                       AND pbs.bac_series_id = ?
                     GROUP BY c.id, c.name, c.description, c.opportunities ORDER BY c.name'
                );
                $careers->execute([$domainId, $seriesId]);
            } else {
                $careers = $db->prepare(
                    'SELECT c.id, c.name, c.description, c.opportunities
                     FROM careers c JOIN program_careers pc ON pc.career_id = c.id
                     JOIN programs p ON p.id = pc.program_id
                     WHERE c.domain_id = ? AND c.is_active = 1 AND p.is_active = 1
                     GROUP BY c.id, c.name, c.description, c.opportunities ORDER BY c.name'
                );
                $careers->execute([$domainId]);
            }
            $domain['careers'] = $careers->fetchAll();
        }
        unset($domain);

        return [
            'test' => $test,
            'domains' => $domains,
            'fallback' => $fallback,
            'bac_series_code' => $profile['bac_series_code'],
        ];
    }
}
