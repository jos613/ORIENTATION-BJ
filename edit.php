<?php
use App\Core\View;

$values = $values ?? $profile;
$selectedSeriesId = (string) ($values['bac_series_id'] ?? '');
?>
<main class="profile-page">
    <section class="profile-banner">
        <div class="container profile-banner-inner">
            <a href="<?= View::escape($basePath) ?>/" class="profile-back">← Retour à l’accueil</a>
            <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Ton espace personnel</span>
            <h1>Mon profil</h1>
            <p>Quelques repères pour que tes pistes d’orientation tiennent compte de ta situation.</p>
        </div>
        <div class="profile-banner-mark" aria-hidden="true">BJ</div>
    </section>

    <section class="container profile-content">
        <div class="profile-progress-card">
            <div class="profile-progress-icon"><img src="<?= View::escape($basePath) ?>/public/assets/img/symbole-orientation-bj.svg" alt="" width="36" height="36"></div>
            <div><strong>Ton profil, ton point de départ</strong><p>Renseigne ta série du BAC avant de commencer le test d’orientation. Tu pourras compléter les autres informations plus tard.</p></div>
            <?php if (!empty($profile['bac_series_id'])): ?><span class="profile-status profile-status-done">Série renseignée</span><?php else: ?><span class="profile-status">À compléter</span><?php endif; ?>
        </div>

        <div class="profile-layout">
            <aside class="profile-aside">
                <span class="profile-aside-label">PROFIL ÉTUDIANT</span>
                <h2><?= View::escape((string) $profile['first_name']) ?><br><?= View::escape((string) $profile['last_name']) ?></h2>
                <p><?= View::escape((string) $currentUser['email']) ?></p>
                <div class="profile-aside-line"></div>
                <span class="profile-aside-tip"><span>✦</span> Tu gardes la maîtrise de tes informations.</span>
            </aside>

            <section class="profile-form-panel" aria-labelledby="profile-form-title">
                <div class="profile-form-heading"><div><span class="eyebrow"><span class="eyebrow-dot"></span> Informations personnelles</span><h2 id="profile-form-title">Fais le point sur ta situation.</h2></div><span class="profile-step">01 <small>/ 01</small></span></div>
                <?php if (!empty($success)): ?><div class="auth-alert auth-alert-success" role="status"><?= View::escape((string) $success) ?></div><?php endif; ?>
                <?php if (!empty($errors)): ?><div class="auth-alert auth-alert-error" role="alert"><strong>Vérifie ces informations :</strong><ul><?php foreach ($errors as $error): ?><li><?= View::escape((string) $error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

                <form action="<?= View::escape($basePath) ?>/profil" method="post" class="profile-form">
                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                    <div class="profile-fields-row">
                        <div class="auth-field"><label for="first_name">Prénom <span>*</span></label><input id="first_name" name="first_name" type="text" value="<?= View::escape((string) ($values['first_name'] ?? '')) ?>" autocomplete="given-name" maxlength="100" required></div>
                        <div class="auth-field"><label for="last_name">Nom <span>*</span></label><input id="last_name" name="last_name" type="text" value="<?= View::escape((string) ($values['last_name'] ?? '')) ?>" autocomplete="family-name" maxlength="100" required></div>
                    </div>
                    <div class="auth-field"><label for="bac_series_id">Série du BAC</label><select id="bac_series_id" name="bac_series_id"><option value="">Sélectionner ma série</option><?php foreach ($series as $item): ?><option value="<?= (int) $item['id'] ?>" <?= $selectedSeriesId === (string) $item['id'] ? 'selected' : '' ?>><?= View::escape((string) $item['code']) ?> — <?= View::escape((string) $item['name']) ?><?= !(bool) $item['is_active'] ? ' (série désactivée)' : '' ?></option><?php endforeach; ?></select><?php if ($series === []): ?><small>Aucune série n’est encore disponible. L’administration pourra en ajouter.</small><?php else: ?><small>Cette information est nécessaire avant le test d’orientation.</small><?php endif; ?></div>
                    <div class="profile-fields-row">
                        <div class="auth-field"><label for="bac_year">Année du BAC</label><input id="bac_year" name="bac_year" type="number" min="1950" max="<?= (int) date('Y') ?>" value="<?= View::escape((string) ($values['bac_year'] ?? '')) ?>" inputmode="numeric" placeholder="Ex. <?= (int) date('Y') ?>"></div>
                        <div class="auth-field"><label for="phone">Téléphone</label><input id="phone" name="phone" type="tel" value="<?= View::escape((string) ($values['phone'] ?? '')) ?>" autocomplete="tel" maxlength="30" placeholder="Ex. +229 …"></div>
                    </div>
                    <div class="auth-field"><label for="city">Ville</label><input id="city" name="city" type="text" value="<?= View::escape((string) ($values['city'] ?? '')) ?>" autocomplete="address-level2" maxlength="120" placeholder="Ta ville de résidence"></div>
                    <div class="profile-form-actions"><span><strong>*</strong> Champs obligatoires</span><button class="btn btn-brand" type="submit">Enregistrer mon profil <span aria-hidden="true">↗</span></button></div>
                </form>
            </section>
        </div>
    </section>
</main>
