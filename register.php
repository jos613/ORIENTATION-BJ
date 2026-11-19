<?php
use App\Core\View;

$asset = $basePath . '/public/assets';
?>
<main class="auth-page">
    <div class="auth-panel auth-panel-image">
        <a class="auth-brand" href="<?= View::escape($basePath) ?>/"><img src="<?= View::escape($asset) ?>/img/logo-orientation-bj-blanc.svg" alt="ORIENTATION-BJ — Ton BAC. Ta voie." width="220" height="56"></a>
        <div class="auth-image-copy"><span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Un nouveau départ</span><h1>Ouvre le champ<br>des possibles.</h1><p>Crée ton espace et commence à bâtir un projet d’orientation qui te ressemble.</p></div>
        <span class="auth-image-caption">ORIENTATION ACADÉMIQUE &amp; PROFESSIONNELLE AU BÉNIN</span>
    </div>
    <div class="auth-panel auth-panel-form auth-panel-register">
        <a class="auth-back" href="<?= View::escape($basePath) ?>/">← Retour à l’accueil</a>
        <div class="auth-form-wrap">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Ton avenir se prépare</span>
            <h2>Créons ton compte.</h2>
            <p class="auth-subtitle">Quelques informations pour commencer ton parcours.</p>

            <?php if (!empty($errors)): ?><div class="auth-alert auth-alert-error" role="alert"><strong>Vérifie ces informations :</strong><ul><?php foreach ($errors as $error): ?><li><?= View::escape((string) $error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

            <form action="<?= View::escape($basePath) ?>/inscription" method="post" class="auth-form">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <div class="auth-field-row">
                    <div class="auth-field"><label for="first_name">Prénom</label><input id="first_name" name="first_name" type="text" value="<?= View::escape((string) ($values['first_name'] ?? '')) ?>" autocomplete="given-name" maxlength="100" placeholder="Ton prénom" required></div>
                    <div class="auth-field"><label for="last_name">Nom</label><input id="last_name" name="last_name" type="text" value="<?= View::escape((string) ($values['last_name'] ?? '')) ?>" autocomplete="family-name" maxlength="100" placeholder="Ton nom" required></div>
                </div>
                <div class="auth-field"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" value="<?= View::escape((string) ($values['email'] ?? '')) ?>" autocomplete="email" maxlength="190" placeholder="exemple@domaine.com" required></div>
                <div class="auth-field"><label for="password">Mot de passe</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="10" maxlength="200" placeholder="10 caractères minimum" required><small>Utilise au moins 10 caractères.</small></div>
                <div class="auth-field"><label for="password_confirmation">Confirmer le mot de passe</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="10" maxlength="200" placeholder="Saisis-le une nouvelle fois" required></div>
                <button class="btn btn-brand auth-submit" type="submit">Créer mon compte <span aria-hidden="true">↗</span></button>
            </form>
            <p class="auth-switch">Tu as déjà un compte ? <a href="<?= View::escape($basePath) ?>/connexion">Me connecter</a></p>
        </div>
        <div class="auth-form-footer"><span>© <?= date('Y') ?> ORIENTATION-BJ</span><span>Tes données restent dans ton espace personnel.</span></div>
    </div>
</main>
