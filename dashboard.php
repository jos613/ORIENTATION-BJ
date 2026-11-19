<?php
use App\Core\Session;
use App\Core\View;
$asset = $basePath . '/public/assets';
$isAdmin = ($currentUser['role'] ?? '') === 'admin';
?>
<div class="dashboard-shell">
    <aside class="dashboard-sidebar" id="dashboardSidebar">
        <a class="dashboard-brand" href="<?= View::escape($basePath) ?>/dashboard"><img src="<?= View::escape($asset) ?>/img/logo-orientation-bj.svg" alt="ORIENTATION-BJ" width="190"></a>
        <div class="sidebar-caption">ESPACE <?= $isAdmin ? 'ADMINISTRATEUR' : 'ÉTUDIANT' ?></div>
        <nav class="dashboard-menu" aria-label="Menu de l’espace personnel">
            <a class="dashboard-link active" href="<?= View::escape($basePath) ?>/dashboard"><span>⌂</span> Tableau de bord</a>
            <a class="dashboard-link" href="<?= View::escape($basePath) ?>/profil"><span>◎</span> Mon profil</a>
            <?php if (!$isAdmin): ?><a class="dashboard-link" href="<?= View::escape($basePath) ?>/test-orientation"><span>✳</span> Test d’orientation</a><a class="dashboard-link" href="<?= View::escape($basePath) ?>/resultats"><span>↗</span> Mes résultats</a><a class="dashboard-link" href="<?= View::escape($basePath) ?>/favoris"><span>♥</span> Mes favoris</a><a class="dashboard-link" href="<?= View::escape($basePath) ?>/historique"><span>◷</span> Historique</a><?php endif; ?>
            <?php if ($isAdmin): ?><a class="dashboard-link" href="<?= View::escape($basePath) ?>/admin/series"><span>✳</span> Séries du BAC</a><a class="dashboard-link" href="<?= View::escape($basePath) ?>/admin/referentiel/domaines"><span>▤</span> Référentiel</a><a class="dashboard-link" href="<?= View::escape($basePath) ?>/admin/utilisateurs"><span>♙</span> Utilisateurs</a><a class="dashboard-link" href="<?= View::escape($basePath) ?>/admin/securite"><span>✓</span> Sécurité</a><?php endif; ?>
        </nav>
        <div class="sidebar-bottom">
            <div class="sidebar-user"><span class="sidebar-avatar"><?= View::escape(mb_strtoupper(mb_substr($currentUser['first_name'], 0, 1))) ?></span><div><strong><?= View::escape($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></strong><small><?= $isAdmin ? 'Administrateur' : 'Étudiant' ?></small></div></div>
            <form action="<?= View::escape($basePath) ?>/deconnexion" method="post"><input type="hidden" name="_csrf_token" value="<?= View::escape(Session::csrfToken()) ?>"><button class="sidebar-logout" type="submit">↪ &nbsp; Déconnexion</button></form>
        </div>
    </aside>
    <main class="dashboard-main">
        <header class="dashboard-topbar"><button class="sidebar-toggle" type="button" aria-label="Ouvrir le menu" aria-controls="dashboardSidebar" aria-expanded="false">☰</button><span><?= $isAdmin ? 'Tableau de bord administrateur' : 'Mon espace personnel' ?></span><span class="dashboard-topbar-mark">ORIENTATION-BJ</span></header>
        <div class="dashboard-content">
            <div class="dashboard-welcome"><div><span class="dashboard-eyebrow"><?= $isAdmin ? 'PILOTAGE DE LA PLATEFORME' : 'TON ESPACE PERSONNEL' ?></span><h1>Bonjour <?= View::escape($currentUser['first_name']) ?> <span>✦</span></h1><p><?= $isAdmin ? 'Voici un aperçu de l’activité et du référentiel ORIENTATION-BJ.' : 'Bienvenue dans ton espace. Avance à ton rythme vers un choix qui te ressemble.' ?></p></div><div class="welcome-orbit" aria-hidden="true">◎</div></div>
            <?php if ($authMessage): ?><div class="alert alert-success" role="status"><?= View::escape($authMessage) ?></div><?php endif; ?>
            <?php if ($isAdmin && $adminStats): ?>
                <section class="admin-analytics" aria-labelledby="admin-analytics-title">
                    <div class="admin-analytics-heading"><div><span class="dashboard-eyebrow">VUE D’ENSEMBLE</span><h2 id="admin-analytics-title">Activité de la plateforme</h2></div><span class="admin-updated">Données actualisées à l’ouverture</span></div>
                    <div class="admin-stat-grid">
                        <article class="admin-stat-card"><span class="admin-stat-icon students">◎</span><div><small>Élèves actifs</small><strong><?= number_format((int) $adminStats['counts']['active_students'], 0, ',', ' ') ?></strong></div><span class="admin-stat-detail">Comptes étudiants</span></article>
                        <article class="admin-stat-card"><span class="admin-stat-icon completed">✓</span><div><small>Tests terminés</small><strong><?= number_format((int) $adminStats['counts']['completed_tests'], 0, ',', ' ') ?></strong></div><span class="admin-stat-detail">Depuis le lancement</span></article>
                        <article class="admin-stat-card"><span class="admin-stat-icon recent">↗</span><div><small>Tests · 30 jours</small><strong><?= number_format((int) $adminStats['counts']['tests_last_30_days'], 0, ',', ' ') ?></strong></div><span class="admin-stat-detail">Terminés récemment</span></article>
                        <article class="admin-stat-card"><span class="admin-stat-icon progress">◷</span><div><small>Tests en cours</small><strong><?= number_format((int) $adminStats['counts']['tests_in_progress'], 0, ',', ' ') ?></strong></div><span class="admin-stat-detail">Tentatives des 7 derniers jours</span></article>
                    </div>
                    <div class="admin-analytics-panels">
                        <article class="admin-chart-card"><div class="admin-panel-heading"><div><h3>Tests terminés</h3><p>Activité des sept derniers jours</p></div><span class="admin-panel-icon">▥</span></div><div class="admin-week-chart" role="img" aria-label="Nombre de tests terminés par jour sur les sept derniers jours">
                            <?php foreach ($adminStats['weekly_tests'] as $day): ?><div class="admin-week-day" title="<?= View::escape($day['date']) ?> : <?= (int) $day['count'] ?> test<?= (int) $day['count'] === 1 ? '' : 's' ?>"><strong><?= (int) $day['count'] ?></strong><div class="admin-bar-track"><i style="height:<?= (int) $day['height'] ?>%"></i></div><small><?= View::escape($day['label']) ?></small></div><?php endforeach; ?>
                        </div></article>
                        <article class="admin-chart-card"><div class="admin-panel-heading"><div><h3>Domaines les plus choisis</h3><p>Première position dans les résultats</p></div><span class="admin-panel-icon star">✦</span></div>
                            <?php if ($adminStats['top_domains']): $maxDomainCount = max(1, (int) $adminStats['top_domains'][0]['tests_count']); ?>
                                <div class="admin-domain-ranking"><?php foreach ($adminStats['top_domains'] as $rank => $domain): ?><div class="admin-domain-row"><div class="admin-domain-label"><span><?= str_pad((string) ($rank + 1), 2, '0', STR_PAD_LEFT) ?></span><strong><?= View::escape($domain['name']) ?></strong><b><?= (int) $domain['tests_count'] ?></b></div><div class="admin-domain-track"><i style="width:<?= max(4, (int) round((int) $domain['tests_count'] / $maxDomainCount * 100)) ?>%"></i></div></div><?php endforeach; ?></div>
                            <?php else: ?><p class="admin-chart-empty">Les domaines principaux apparaîtront après les premières validations de test.</p><?php endif; ?>
                        </article>
                    </div>
                    <div class="admin-catalog-summary"><span>Référentiel actif</span><strong><?= (int) $adminStats['counts']['active_programs'] ?> formations</strong><strong><?= (int) $adminStats['counts']['active_institutions'] ?> établissements</strong><strong><?= (int) $adminStats['counts']['active_careers'] ?> métiers</strong></div>
                </section>
            <?php endif; ?>
            <section class="dashboard-grid" aria-label="Raccourcis personnels">
                <a class="dashboard-card profile-card" href="<?= View::escape($basePath) ?>/profil"><span class="dash-card-icon">◎</span><span class="dash-card-label">MON PROFIL</span><h2>Fais le point sur ta situation</h2><p>Complète tes informations et renseigne ta série du BAC pour préparer la suite.</p><span class="dash-card-cta">Compléter mon profil <b>↗</b></span></a>
                <a class="dashboard-card journey-card" href="<?= View::escape($basePath) ?>/test-orientation"><span class="dash-card-icon">✳</span><span class="dash-card-label">TON PARCOURS</span><h2>Ton orientation commence ici</h2><p>Un chemin guidé pour mieux te connaître et découvrir des pistes d’études et de métiers.</p><span class="dash-card-cta">Accéder au test <b>↗</b></span></a>
                <?php if ($isAdmin): ?><a class="dashboard-card admin-card" href="<?= View::escape($basePath) ?>/admin/series"><span class="dash-card-icon">✦</span><span class="dash-card-label">ADMINISTRATION</span><h2>Gère les séries du BAC</h2><p>Configure les séries proposées aux étudiants dans leur profil.</p><span class="dash-card-cta">Ouvrir la gestion <b>↗</b></span></a><a class="dashboard-card admin-card" href="<?= View::escape($basePath) ?>/admin/referentiel/domaines"><span class="dash-card-icon">▤</span><span class="dash-card-label">CATALOGUE</span><h2>Administre le référentiel</h2><p>Actualise les domaines, formations, établissements et métiers disponibles.</p><span class="dash-card-cta">Gérer le catalogue <b>↗</b></span></a><?php endif; ?>
            </section>
            <div class="dashboard-note"><span>✦</span><p>Chaque parcours commence par une meilleure connaissance de soi. Ton profil est ton premier repère.</p></div>
        </div>
    </main>
</div>
<script>
document.querySelector('.sidebar-toggle')?.addEventListener('click', function () { const open = document.body.classList.toggle('sidebar-open'); this.setAttribute('aria-expanded', String(open)); });
</script>
