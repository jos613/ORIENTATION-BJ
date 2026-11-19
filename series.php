<?php
use App\Core\View;

$values = $values ?? ['code' => '', 'name' => '', 'description' => '', 'is_active' => 1];
$editing = $editSeries !== null;
?>
<main class="series-page">
    <section class="series-banner"><div class="container series-banner-inner"><a href="<?= View::escape($basePath) ?>/" class="profile-back">← Retour à l’accueil</a><span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Administration du référentiel</span><h1>Séries du BAC</h1><p>Gère les séries proposées lors de la constitution du profil.</p></div><div class="profile-banner-mark" aria-hidden="true">01</div></section>
    <section class="container series-content">
        <?php if (!empty($success)): ?><div class="auth-alert auth-alert-success" role="status"><?= View::escape((string) $success) ?></div><?php endif; ?>
        <?php if (!empty($errors)): ?><div class="auth-alert auth-alert-error" role="alert"><strong>À vérifier :</strong><ul><?php foreach ($errors as $error): ?><li><?= View::escape((string) $error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <div class="series-heading"><div><span class="eyebrow"><span class="eyebrow-dot"></span> Référentiel</span><h2><?= count($seriesList) ?> série<?= count($seriesList) > 1 ? 's' : '' ?> enregistrée<?= count($seriesList) > 1 ? 's' : '' ?></h2></div><span class="series-heading-note">Les séries désactivées ne sont plus proposées aux nouveaux profils.</span></div>
        <div class="series-layout">
            <section class="series-list" aria-label="Liste des séries du BAC">
                <?php if ($seriesList === []): ?><div class="series-empty"><span>✦</span><h3>Le référentiel est encore vide.</h3><p>Ajoute les séries du BAC utilisées par la plateforme.</p></div><?php endif; ?>
                <?php foreach ($seriesList as $item): ?>
                    <article class="series-item">
                        <div class="series-item-code"><?= View::escape((string) $item['code']) ?></div>
                        <div class="series-item-info"><div class="series-item-title"><h3><?= View::escape((string) $item['name']) ?></h3><span class="series-badge <?= (bool) $item['is_active'] ? 'series-badge-active' : 'series-badge-off' ?>"><?= (bool) $item['is_active'] ? 'Active' : 'Désactivée' ?></span></div><p><?= View::escape((string) ($item['description'] ?? '')) ?: 'Aucune description pour le moment.' ?></p><small><?= (int) $item['profile_count'] ?> profil<?= (int) $item['profile_count'] > 1 ? 's' : '' ?> associé<?= (int) $item['profile_count'] > 1 ? 's' : '' ?></small></div>
                        <div class="series-item-actions">
                            <a class="series-action-link" href="<?= View::escape($basePath) ?>/admin/series?edit=<?= (int) $item['id'] ?>">Modifier</a>
                            <form action="<?= View::escape($basePath) ?>/admin/series" method="post"><input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="series_id" value="<?= (int) $item['id'] ?>"><button class="series-action-button" type="submit"><?= (bool) $item['is_active'] ? 'Désactiver' : 'Activer' ?></button></form>
                            <form action="<?= View::escape($basePath) ?>/admin/series" method="post" onsubmit="return confirm('Supprimer cette série du référentiel ?')"><input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="series_id" value="<?= (int) $item['id'] ?>"><button class="series-action-button series-action-danger" type="submit" <?= (int) $item['profile_count'] > 0 ? 'disabled title="Utilisée par un profil"' : '' ?>>Supprimer</button></form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

            <aside class="series-form-card">
                <span class="eyebrow"><span class="eyebrow-dot"></span> <?= $editing ? 'Modification' : 'Nouvelle entrée' ?></span>
                <h2><?= $editing ? 'Modifier une série' : 'Ajouter une série' ?></h2>
                <p><?= $editing ? 'Mets à jour les informations du référentiel.' : 'Les codes et noms seront proposés dans le profil du bachelier.' ?></p>
                <form action="<?= View::escape($basePath) ?>/admin/series" method="post" class="auth-form">
                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                    <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
                    <?php if ($editing): ?><input type="hidden" name="series_id" value="<?= (int) $editSeries['id'] ?>"><?php endif; ?>
                    <div class="auth-field"><label for="code">Code de la série</label><input id="code" name="code" type="text" value="<?= View::escape((string) ($values['code'] ?? '')) ?>" maxlength="20" pattern="[A-Za-z0-9_-]{1,20}" placeholder="Ex. A1, C, D" required><small>Lettres, chiffres, tirets et soulignements.</small></div>
                    <div class="auth-field"><label for="name">Nom de la série</label><input id="name" name="name" type="text" value="<?= View::escape((string) ($values['name'] ?? '')) ?>" maxlength="120" placeholder="Nom affiché aux étudiants" required></div>
                    <div class="auth-field"><label for="description">Description <span>(facultative)</span></label><textarea id="description" name="description" rows="4" maxlength="10000" placeholder="Repères ou informations utiles"><?= View::escape((string) ($values['description'] ?? '')) ?></textarea></div>
                    <label class="series-checkbox"><input type="checkbox" name="is_active" value="1" <?= !isset($values['is_active']) || (bool) $values['is_active'] ? 'checked' : '' ?>><span>Proposer cette série aux nouveaux profils</span></label>
                    <div class="series-form-actions"><button class="btn btn-brand" type="submit"><?= $editing ? 'Enregistrer les changements' : 'Ajouter la série' ?></button><?php if ($editing): ?><a href="<?= View::escape($basePath) ?>/admin/series">Annuler</a><?php endif; ?></div>
                </form>
            </aside>
        </div>
    </section>
</main>
