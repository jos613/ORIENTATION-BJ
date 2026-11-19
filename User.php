<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DomainException;
use PDO;

final class User
{
    public static function findForLogin(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT u.id, u.email, u.password_hash, u.role, u.status, p.first_name, p.last_name
             FROM users u
             LEFT JOIN student_profiles p ON p.user_id = u.id
             WHERE u.email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public static function emailExists(string $email): bool
    {
        $statement = Database::connection()->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        return (bool) $statement->fetchColumn();
    }

    public static function createStudent(string $email, string $passwordHash, string $firstName, string $lastName): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $userStatement = $pdo->prepare(
                "INSERT INTO users (email, password_hash, role, status) VALUES (:email, :password_hash, 'student', 'active')"
            );
            $userStatement->execute(['email' => $email, 'password_hash' => $passwordHash]);
            $userId = (int) $pdo->lastInsertId();

            $profileStatement = $pdo->prepare(
                'INSERT INTO student_profiles (user_id, first_name, last_name) VALUES (:user_id, :first_name, :last_name)'
            );
            $profileStatement->execute([
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            $pdo->commit();
            return $userId;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public static function findForSession(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT u.id, u.email, u.role, u.status, p.first_name, p.last_name
             FROM users u LEFT JOIN student_profiles p ON p.user_id = u.id
             WHERE u.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $userId]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public static function touchLogin(int $userId): void
    {
        $statement = Database::connection()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
        $statement->execute([$userId]);
    }

    public static function adminList(string $search = '', string $role = 'all'): array
    {
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(u.email LIKE :search_email OR p.first_name LIKE :search_first OR p.last_name LIKE :search_last)';
            $like = '%' . $search . '%';
            $params += ['search_email' => $like, 'search_first' => $like, 'search_last' => $like];
        }
        if (in_array($role, ['student', 'admin'], true)) {
            $where[] = 'u.role = :role';
            $params['role'] = $role;
        }
        $sql = 'SELECT u.id, u.email, u.role, u.status, u.created_at, u.last_login_at,
                       p.first_name, p.last_name,
                       (SELECT COUNT(*) FROM orientation_tests t WHERE t.user_id = u.id AND t.status = \'completed\') AS completed_tests
                FROM users u LEFT JOIN student_profiles p ON p.user_id = u.id'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY (u.status = \'active\') DESC, u.created_at DESC, u.id DESC LIMIT 250';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public static function updateAccess(int $actorId, int $userId, string $role, string $status, ?string $ip): void
    {
        if (!in_array($role, ['student', 'admin'], true) || !in_array($status, ['active', 'disabled'], true)) {
            throw new DomainException('Le rôle ou le statut demandé est invalide.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $statement = $db->prepare('SELECT id, role, status FROM users WHERE id = ? FOR UPDATE');
            $statement->execute([$userId]);
            $target = $statement->fetch();
            if (!$target) throw new DomainException('Ce compte est introuvable.');

            if ($target['role'] === $role && $target['status'] === $status) {
                $db->commit();
                return;
            }

            if ($actorId === $userId && ($role !== 'admin' || $status !== 'active')) {
                throw new DomainException('Tu ne peux pas retirer tes propres droits administrateur ni désactiver ta session.');
            }

            $removesActiveAdmin = $target['role'] === 'admin' && $target['status'] === 'active'
                && ($role !== 'admin' || $status !== 'active');
            if ($removesActiveAdmin) {
                $admins = $db->query("SELECT id FROM users WHERE role = 'admin' AND status = 'active' FOR UPDATE")->fetchAll(PDO::FETCH_COLUMN);
                if (count($admins) <= 1) {
                    throw new DomainException('Le dernier compte administrateur actif doit être conservé.');
                }
            }

            $statement = $db->prepare('UPDATE users SET role = ?, status = ? WHERE id = ?');
            $statement->execute([$role, $status, $userId]);
            AuditLog::record($actorId, 'user.access_updated', 'user', $userId, [
                'previous_role' => $target['role'],
                'new_role' => $role,
                'previous_status' => $target['status'],
                'new_status' => $status,
            ], $ip, $db);
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) $db->rollBack();
            throw $exception;
        }
    }
}
