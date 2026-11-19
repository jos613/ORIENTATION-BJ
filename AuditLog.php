<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class AuditLog
{
    public static function record(
        ?int $actorId,
        string $action,
        string $entityType,
        ?int $entityId,
        array $details = [],
        ?string $ipAddress = null,
        ?PDO $connection = null
    ): void {
        $db = $connection ?? Database::connection();
        $statement = $db->prepare(
            'INSERT INTO admin_audit_logs (actor_user_id, action, entity_type, entity_id, details, ip_address)
             VALUES (:actor, :action, :entity_type, :entity_id, :details, :ip)'
        );
        $statement->execute([
            'actor' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'ip' => $ipAddress,
        ]);
    }

    public static function recent(int $limit = 100): array
    {
        $limit = max(1, min(250, $limit));
        return Database::connection()->query(
            'SELECT l.id, l.action, l.entity_type, l.entity_id, l.details, l.ip_address, l.created_at,
                    u.email AS actor_email, p.first_name AS actor_first_name, p.last_name AS actor_last_name
             FROM admin_audit_logs l
             LEFT JOIN users u ON u.id = l.actor_user_id
             LEFT JOIN student_profiles p ON p.user_id = u.id
             ORDER BY l.created_at DESC, l.id DESC LIMIT ' . $limit
        )->fetchAll();
    }
}
