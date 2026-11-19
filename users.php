<?php use App\Core\View; ?>
<section class="admin-users-page">
    <header class="admin-users-hero">
        <div><span class="dashboard-eyebrow">ADMINISTRATION ET SÉCURITÉ</span><h1>Utilisateurs et accès</h1><p>Gère les rôles et l’accès aux comptes. Un compte désactivé ne peut plus ouvrir de session.</p></div>
        <a class="audit-shortcut" href="<?= View::escape($basePath) ?>/admin/securite">Voir le journal des actions <span>↗</span></a>
    </header>

    <?php if ($success): ?><div class="test-alert success" role="status"><?= View::escape($success) ?></div><?php endif; ?>
    <?php if ($errors): ?><div class="test-alert error" role="alert"><?php foreach ($errors as $error): ?><p><?= View::escape($error) ?></p><?php endforeach; ?></div><?php endif; ?>

    <form class="admin-user-filters" method="get" action="<?= View::escape($basePath) ?>/admin/utilisateurs">
        <label><span>Rechercher un compte</span><input type="search" name="q" value="<?= View::escape($search) ?>" maxlength="100" placeholder="Nom ou adresse e-mail"></label>
        <label><span>Rôle</span><select name="role"><option value="all" <?= $roleFilter === 'all' ? 'selected' : '' ?>>Tous</option><option value="student" <?= $roleFilter === 'student' ? 'selected' : '' ?>>Élèves</option><option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Administrateurs</option></select></label>
        <button type="submit">Filtrer</button>
        <?php if ($search !== '' || $roleFilter !== 'all'): ?><a href="<?= View::escape($basePath) ?>/admin/utilisateurs">Effacer</a><?php endif; ?>
    </form>

    <div class="admin-user-list-heading"><div><h2>Comptes enregistrés</h2><p><?= count($users) ?> compte<?= count($users) === 1 ? '' : 's' ?><?= count($users) === 250 ? ' · limite d’affichage atteinte, précise la recherche' : '' ?></p></div><span class="admin-user-privacy">Les mots de passe ne sont jamais affichés.</span></div>
    <?php if ($users): ?>
        <div class="admin-user-list">
            <?php foreach ($users as $account): $isSelf = (int) $account['id'] === (int) $currentUser['id']; ?>
                <article class="admin-user-row">
                    <div class="admin-user-identity"><span class="admin-user-avatar"><?= View::escape(mb_strtoupper(mb_substr((string) ($account['first_name'] ?: $account['email']), 0, 1))) ?></span><div><strong><?= View::escape(trim((string) ($account['first_name'] . ' ' . $account['last_name'])) ?: 'Nom non renseigné') ?><?= $isSelf ? ' · toi' : '' ?></strong><small><?= View::escape($account['email']) ?></small></div></div>
                    <div class="admin-user-activity"><span class="admin-user-role <?= View::escape($account['role']) ?>"><?= $account['role'] === 'admin' ? 'Administrateur' : 'Élève' ?></span><span class="admin-user-status <?= View::escape($account['status']) ?>"><?= $account['status'] === 'active' ? 'Actif' : 'Désactivé' ?></span><small><?= (int) $account['completed_tests'] ?> test<?= (int) $account['completed_tests'] === 1 ? '' : 's' ?> terminé<?= (int) $account['completed_tests'] === 1 ? '' : 's' ?></small><small>Dernière connexion : <?= $account['last_login_at'] ? View::escape(date('d/m/Y H:i', strtotime((string) $account['last_login_at']))) : 'jamais' ?></small></div>
                    <form class="admin-user-access-form" method="post" action="<?= View::escape($basePath) ?>/admin/utilisateurs">
                        <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>"><input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>"><input type="hidden" name="return_q" value="<?= View::escape($search) ?>"><input type="hidden" name="return_role" value="<?= View::escape($roleFilter) ?>">
                        <label><span>Rôle</span><select name="role" <?= $isSelf ? 'disabled' : '' ?>><option value="student" <?= $account['role'] === 'student' ? 'selected' : '' ?>>Élève</option><option value="admin" <?= $account['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option></select></label>
                        <label><span>Accès</span><select name="status" <?= $isSelf ? 'disabled' : '' ?>><option value="active" <?= $account['status'] === 'active' ? 'selected' : '' ?>>Actif</option><option value="disabled" <?= $account['status'] === 'disabled' ? 'selected' : '' ?>>Désactivé</option></select></label>
                        <?php if ($isSelf): ?><span class="admin-self-lock">Ton propre accès est protégé.</span><?php else: ?><button type="submit">Enregistrer</button><?php endif; ?>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="admin-users-empty"><span>◎</span><h2>Aucun compte trouvé</h2><p>Modifie les critères de recherche pour afficher d’autres comptes.</p></div>
    <?php endif; ?>
</section>
