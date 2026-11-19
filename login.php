<?php
use App\Core\View;

$asset = $basePath . '/public/assets';
?>
<main class="auth-page">
    <div class="auth-panel auth-panel-image">
        <a class="auth-brand" href="<?= View::escape($basePath) ?>/"><img src="<?= View::escape($asset) ?>/img/logo-orientation-bj-blanc.svg" alt="ORIENTATION-BJ — Ton BAC. Ta voie." width="220" height="56"></a>
        <div class="auth-image-copy"><span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Ton BAC. Ta voie.</span><h1>Heureux de te<br>retrouver.</h1><p>Reprends le fil de ton parcours et continue à explorer les possibilités qui te ressemblent.</p></div>
        <span class="auth-image-caption">ORIENTATION ACADÉMIQUE &amp; PROFESSIONNELLE AU BÉNIN</span>
    </div>
    <div class="auth-panel auth-panel-form">
        <a class="auth-back" href="<?= View::escape($basePath) ?>/">← Retour à l’accueil</a>
        <div class="auth-form-wrap">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Ton espace personnel</span>
            <h2>Content de te revoir.</h2>
            <p class="auth-subtitle">Connecte-toi pour retrouver ton parcours d’orientation.</p>

            <?php if (!empty($success)): ?><div class="auth-alert auth-alert-success" role="status"><?= View::escape((string) $success) ?></div><?php endif; ?>
            <?php if (!empty($error)): ?><div class="auth-alert auth-alert-error" role="alert"><?= View::escape((string) $error) ?></div><?php endif; ?>

            <form action="<?= View::escape($basePath) ?>/connexion" method="post" class="auth-form">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <div class="auth-field"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" value="<?= View::escape((string) $email) ?>" autocomplete="email" placeholder="exemple@domaine.com" maxlength="190" required></div>
                <div class="auth-field"><label for="password">Mot de passe</label><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Ton mot de passe" required></div>
                <button class="btn btn-brand auth-submit" type="submit">Me connecter <span aria-hidden="true">↗</span></button>
            </form>
            <p class="auth-switch">Tu n’as pas encore de compte ? <a href="<?= View::escape($basePath) ?>/inscription">Créer mon compte</a></p>
        </div>
        <div class="auth-form-footer"><span>© <?= date('Y') ?> ORIENTATION-BJ</span><span>Chaque parcours commence par un pas.</span></div>
    </div>
</main>
