<?php
use App\Core\Session;
use App\Core\View;
$isAdmin = ($currentUser['role'] ?? '') === 'admin';
?>
<div class="dashboard-shell">
    <aside class="dashboard-sidebar" id="dashboardSidebar">
        <a class="dashboard-brand" href="<?= View::escape($basePath) ?>/dashboard"><img src="<?= View::escape($basePath) ?>/public/assets/img/logo-orientation-bj.svg" alt="ORIENTATION-BJ" width="190"></a>
        <div class="sidebar-caption">ESPACE <?= $isAdmin ? 'ADMINISTRATEUR' : 'ÉTUDIANT' ?></div>
        <nav class="dashboard-menu" aria-label="Menu de l’espace personnel">
            <a class="dashboard-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/dashboard"><span>⌂</span> Tableau de bord</a>
            <a class="dashboard-link <?= $activeMenu === 'profile' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/profil"><span>◎</span> Mon profil</a>
            <?php if (!$isAdmin): ?><a class="dashboard-link <?= $activeMenu === 'test' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/test-orientation"><span>✳</span> Test d’orientation</a><?php endif; ?>
            <?php if (!$isAdmin): ?><a class="dashboard-link <?= $activeMenu === 'results' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/resultats"><span>↗</span> Mes résultats</a><?php endif; ?>
            <?php if (!$isAdmin): ?><a class="dashboard-link <?= $activeMenu === 'favorites' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/favoris"><span>♥</span> Mes favoris</a><?php endif; ?>
            <?php if (!$isAdmin): ?><a class="dashboard-link <?= $activeMenu === 'history' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/historique"><span>◷</span> Historique</a><?php endif; ?>
            <?php if ($isAdmin): ?><a class="dashboard-link <?= $activeMenu === 'series' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/admin/series"><span>✳</span> Séries du BAC</a><?php endif; ?>
            <?php if ($isAdmin): ?><a class="dashboard-link <?= $activeMenu === 'catalog' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/admin/referentiel/domaines"><span>▤</span> Référentiel</a><?php endif; ?>
            <?php if ($isAdmin): ?><a class="dashboard-link <?= $activeMenu === 'users' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/admin/utilisateurs"><span>♙</span> Utilisateurs</a><a class="dashboard-link <?= $activeMenu === 'security' ? 'active' : '' ?>" href="<?= View::escape($basePath) ?>/admin/securite"><span>✓</span> Sécurité</a><?php endif; ?>
        </nav>
        <div class="sidebar-bottom"><div class="sidebar-user"><span class="sidebar-avatar"><?= View::escape(mb_strtoupper(mb_substr($currentUser['first_name'], 0, 1))) ?></span><div><strong><?= View::escape($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></strong><small><?= $isAdmin ? 'Administrateur' : 'Étudiant' ?></small></div></div>
            <form action="<?= View::escape($basePath) ?>/deconnexion" method="post"><input type="hidden" name="_csrf_token" value="<?= View::escape(Session::csrfToken()) ?>"><button class="sidebar-logout" type="submit">↪ &nbsp; Déconnexion</button></form>
        </div>
    </aside>
    <main class="dashboard-main"><header class="dashboard-topbar"><button class="sidebar-toggle" type="button" aria-label="Ouvrir le menu" aria-controls="dashboardSidebar" aria-expanded="false">☰</button><span><?= View::escape($pageTitle ?? 'Mon espace personnel') ?></span><span class="dashboard-topbar-mark">ORIENTATION-BJ</span></header><div class="dashboard-content dashboard-page-content"><?= $content ?></div></main>
</div>
<script>document.querySelector('.sidebar-toggle')?.addEventListener('click',function(){const open=document.body.classList.toggle('sidebar-open');this.setAttribute('aria-expanded',String(open));});</script>
