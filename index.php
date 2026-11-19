<?php use App\Core\View; ?>
<section class="history-page">
    <header class="history-hero">
        <span class="dashboard-eyebrow">TON PARCOURS D’ORIENTATION</span>
        <h1>Historique de mes tests</h1>
        <p>Retrouve tes anciennes tentatives, compare les domaines qui sont ressortis et reprends ton parcours quand tu le souhaites.</p>
    </header>

    <?php if ($items): ?>
        <div class="history-summary"><?= count($items) ?> tentative<?= count($items) > 1 ? 's' : '' ?> enregistrée<?= count($items) > 1 ? 's' : '' ?></div>
        <div class="history-list">
            <?php foreach ($items as $item):
                $status = (string) $item['status'];
                $statusLabel = match ($status) {
                    'completed' => 'Terminé',
                    'in_progress' => 'En cours',
                    default => 'Interrompu',
                };
                $date = $item['completed_at'] ?: $item['started_at'];
            ?>
                <article class="history-card">
                    <div class="history-card-status <?= View::escape($status) ?>" aria-hidden="true"><?= $status === 'completed' ? '✓' : ($status === 'in_progress' ? '…' : '↺') ?></div>
                    <div class="history-card-main">
                        <div class="history-card-heading">
                            <h2>Test du <?= View::escape(date('d/m/Y', strtotime((string) $date))) ?></h2>
                            <span class="history-status-pill <?= View::escape($status) ?>"><?= View::escape($statusLabel) ?></span>
                        </div>
                        <p><?= View::escape(date('H:i', strtotime((string) $date))) ?> · <?= (int) $item['answered_count'] ?> réponse<?= (int) $item['answered_count'] > 1 ? 's' : '' ?> enregistrée<?= (int) $item['answered_count'] > 1 ? 's' : '' ?></p>
                        <?php if ($status === 'completed' && $item['top_domain_name']): ?>
                            <div class="history-highlight"><span>Domaine principal</span><strong><?= View::escape($item['top_domain_name']) ?></strong><?php if ($item['top_domain_percentage'] !== null): ?><b><?= number_format((float) $item['top_domain_percentage'], 2, ',', ' ') ?> %</b><?php endif; ?></div>
                        <?php elseif ($status === 'in_progress'): ?>
                            <div class="history-progress"><span>Progression</span><strong><?= (int) $item['answered_count'] ?> / <?= (int) $item['question_count'] ?> questions</strong></div>
                        <?php endif; ?>
                    </div>
                    <div class="history-card-action">
                        <?php if ($status === 'completed'): ?>
                            <a href="<?= View::escape($basePath) ?>/resultats?test=<?= (int) $item['id'] ?>">Voir les résultats <span>↗</span></a>
                        <?php else: ?>
                            <a href="<?= View::escape($basePath) ?>/test-orientation"><?= $status === 'in_progress' ? 'Reprendre le test' : 'Commencer un nouveau test' ?> <span>↗</span></a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <article class="history-empty">
            <div class="history-empty-icon" aria-hidden="true">◷</div>
            <h2>Ton historique commencera ici</h2>
            <p>Une fois ton premier test commencé, tes tentatives apparaîtront dans cet espace.</p>
            <a class="test-primary" href="<?= View::escape($basePath) ?>/test-orientation">Accéder au test <span>↗</span></a>
        </article>
    <?php endif; ?>
</section>
