<?php
use App\Core\View;
$actionLabels = [
    'user.access_updated' => 'Accès à un compte modifié',
    'catalog.status_changed' => 'Statut d’une donnée modifié',
    'catalog.deleted' => 'Donnée du référentiel supprimée',
    'catalog.created' => 'Donnée du référentiel créée',
    'catalog.updated' => 'Donnée du référentiel modifiée',
    'series.status_changed' => 'Statut d’une série modifié',
    'series.deleted' => 'Série du BAC supprimée',
    'series.created' => 'Série du BAC créée',
    'series.updated' => 'Série du BAC modifiée',
];
$entityLabels = ['user' => 'Compte', 'domaines' => 'Domaine', 'formations' => 'Formation', 'etablissements' => 'Établissement', 'metiers' => 'Métier', 'bac_series' => 'Série du BAC'];
$detailLabels = ['previous_role' => 'Ancien rôle', 'new_role' => 'Nouveau rôle', 'previous_status' => 'Ancien accès', 'new_status' => 'Nouvel accès', 'from' => 'Avant', 'to' => 'Après', 'name' => 'Nom', 'code' => 'Code'];
?>
<section class="admin-security-page">
    <header class="admin-users-hero">
        <div><span class="dashboard-eyebrow">CONTRÔLE ET TRAÇABILITÉ</span><h1>Journal des actions sensibles</h1><p>Les changements d’accès et les opérations d’administration sont consignés pour permettre leur vérification.</p></div>
        <a class="audit-shortcut" href="<?= View::escape($basePath) ?>/admin/utilisateurs">Gestion des utilisateurs <span>↗</span></a>
    </header>

    <?php if ($logs): ?>
        <div class="audit-log-list">
            <?php foreach ($logs as $log): $details = json_decode((string) $log['details'], true); $details = is_array($details) ? $details : []; ?>
                <article class="audit-log-row">
                    <span class="audit-log-icon" aria-hidden="true">✓</span>
                    <div class="audit-log-main"><div class="audit-log-heading"><strong><?= View::escape($actionLabels[$log['action']] ?? $log['action']) ?></strong><span><?= View::escape($entityLabels[$log['entity_type']] ?? $log['entity_type']) ?><?= $log['entity_id'] ? ' #' . (int) $log['entity_id'] : '' ?></span></div><p>Par <?= View::escape(trim((string) ($log['actor_first_name'] . ' ' . $log['actor_last_name'])) ?: (string) ($log['actor_email'] ?? 'Compte supprimé')) ?> · <?= View::escape(date('d/m/Y à H:i', strtotime((string) $log['created_at']))) ?></p>
                        <?php if ($details): ?><div class="audit-log-details"><?php foreach ($details as $key => $value): if (is_scalar($value) || $value === null): $displayValue = $value === null ? '—' : (string) $value; if (in_array($key, ['previous_role','new_role'], true)) $displayValue = ['admin'=>'Administrateur','student'=>'Élève'][$displayValue] ?? $displayValue; if (in_array($key, ['previous_status','new_status'], true)) $displayValue = ['active'=>'Actif','disabled'=>'Désactivé'][$displayValue] ?? $displayValue; ?><span><b><?= View::escape($detailLabels[$key] ?? (string) $key) ?> :</b> <?= View::escape($displayValue) ?></span><?php endif; endforeach; ?></div><?php endif; ?>
                    </div>
                    <?php if ($log['ip_address']): ?><small class="audit-log-ip">IP <?= View::escape($log['ip_address']) ?></small><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="admin-users-empty"><span>◷</span><h2>Aucune action enregistrée</h2><p>Les prochaines modifications administratives apparaîtront ici.</p></div>
    <?php endif; ?>
</section>
