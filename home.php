<?php
use App\Core\View;
use App\Core\Session;

$asset = $basePath . '/public/assets';
?>
<header class="site-header">
    <nav class="navbar navbar-expand-lg site-nav" aria-label="Navigation principale">
        <div class="container">
            <a class="navbar-brand brand-link" href="#accueil" aria-label="ORIENTATION-BJ, accueil"><img src="<?= View::escape($asset) ?>/img/logo-orientation-bj.svg" alt="ORIENTATION-BJ — Ton BAC. Ta voie." width="224" height="56"></a>
            <button class="navbar-toggler nav-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Ouvrir le menu"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="mainNavigation">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#parcours">Le parcours</a></li>
                    <li class="nav-item"><a class="nav-link" href="#explorer">Explorer</a></li>
                    <li class="nav-item"><a class="nav-link" href="#engagement">Notre engagement</a></li>
                    <?php if ($currentUser !== null): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= View::escape($basePath) ?>/profil">Mon profil</a></li>
                        <?php if ($currentUser['role'] === 'admin'): ?><li class="nav-item"><a class="nav-link" href="<?= View::escape($basePath) ?>/admin/series">Séries du BAC</a></li><?php endif; ?>
                        <li class="nav-item"><span class="nav-link nav-greeting">Bonjour, <?= View::escape($currentUser['first_name']) ?></span></li>
                        <li class="nav-item ms-lg-2"><form action="<?= View::escape($basePath) ?>/deconnexion" method="post"><input type="hidden" name="_csrf_token" value="<?= View::escape(Session::csrfToken()) ?>"><button class="btn btn-brand-outline" type="submit">Déconnexion</button></form></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= View::escape($basePath) ?>/connexion">Connexion</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-brand-outline" href="<?= View::escape($basePath) ?>/inscription">Créer mon compte</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="accueil">
    <?php if ($authMessage || $notice): ?>
        <div class="container home-notice" role="status"><?= View::escape((string) ($authMessage ?? $notice)) ?></div>
    <?php endif; ?>
    <section class="hero-section" aria-labelledby="hero-title">
        <div class="hero-grain" aria-hidden="true"></div>
        <div class="container hero-container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-7 hero-copy reveal">
                    <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> L’orientation commence par toi</span>
                    <h1 id="hero-title">Ton BAC.<br><span>Ta voie.</span></h1>
                    <p class="hero-lead">Tu as ton bac, maintenant les choix s’ouvrent. Avance avec des repères clairs pour trouver les études et les métiers qui te ressemblent.</p>
                    <div class="hero-actions"><a class="btn btn-sun btn-lg" href="#parcours">Découvrir mon parcours <span aria-hidden="true">↗</span></a><a class="hero-text-link" href="#explorer">Explorer les possibilités <span aria-hidden="true">↓</span></a></div>
                    <div class="hero-proof"><div class="proof-avatars" aria-hidden="true"><span>O</span><span>BJ</span><span>+</span></div><p>Des choix éclairés, à ton rythme<br><strong>et selon ton profil.</strong></p></div>
                </div>
                <div class="col-lg-5 hero-visual reveal reveal-delay">
                    <div class="hero-orbit orbit-one" aria-hidden="true"></div><div class="hero-orbit orbit-two" aria-hidden="true"></div>
                    <img class="hero-emblem" src="<?= View::escape($asset) ?>/img/symbole-orientation-bj.svg" alt="" width="126" height="126">
                    <div class="orientation-card">
                        <div class="orientation-card-top"><span class="mini-label">TON PARCOURS</span><span class="card-sparkle" aria-hidden="true">✳</span></div>
                        <div class="path-step"><span class="path-number">01</span><div><strong>Ton profil</strong><small>Ce qui te définit</small></div><span class="path-check">✓</span></div><div class="path-line"></div>
                        <div class="path-step"><span class="path-number path-number-coral">02</span><div><strong>Tes intérêts</strong><small>Ce qui te motive</small></div><span class="path-pulse"></span></div><div class="path-line"></div>
                        <div class="path-step"><span class="path-number path-number-aqua">03</span><div><strong>Tes possibilités</strong><small>Les voies à explorer</small></div><span class="path-arrow">↗</span></div>
                        <div class="card-footnote"><span class="footnote-icon">✦</span> Un chemin construit autour de toi</div>
                    </div>
                    <div class="float-chip"><span class="float-chip-icon">✦</span><span><strong>Un cap plus clair</strong><small>Commence ici</small></span></div>
                </div>
            </div>
            <div class="hero-bottom-note"><span>01 / 03</span><span class="note-rule"></span><span>ORIENTATION ACADÉMIQUE &amp; PROFESSIONNELLE AU BÉNIN</span></div>
        </div>
        <a class="scroll-cue" href="#explorer" aria-label="Défiler vers la section suivante"><span></span></a>
    </section>

    <section class="intro-section section-space" id="explorer" aria-labelledby="intro-title">
        <div class="container">
            <div class="row align-items-end gy-4 mb-5"><div class="col-lg-7 reveal"><span class="eyebrow"><span class="eyebrow-dot"></span> Une boussole pour la suite</span><h2 id="intro-title" class="section-title">Le bon choix commence<br class="d-none d-md-block"> par les bonnes <em>questions.</em></h2></div><div class="col-lg-5 reveal reveal-delay"><p class="section-intro">Entre domaines, formations et métiers, il existe plusieurs chemins possibles. ORIENTATION-BJ t’aide à les explorer en tenant compte de ton profil et de tes centres d’intérêt.</p></div></div>
            <div class="row g-4 feature-grid">
                <div class="col-md-6 col-xl-3 reveal"><article class="feature-card feature-indigo"><div class="feature-icon"><img src="<?= View::escape($asset) ?>/icons/test.svg" alt="" width="30" height="30"></div><span class="feature-index">01</span><h3>Apprends à te connaître</h3><p>Un questionnaire pour faire ressortir tes intérêts et tes affinités.</p><span class="feature-mark" aria-hidden="true">↗</span></article></div>
                <div class="col-md-6 col-xl-3 reveal reveal-delay"><article class="feature-card feature-coral"><div class="feature-icon"><img src="<?= View::escape($asset) ?>/icons/domaines.svg" alt="" width="30" height="30"></div><span class="feature-index">02</span><h3>Explore les domaines</h3><p>Découvre les grands univers d’études adaptés à tes résultats.</p><span class="feature-mark" aria-hidden="true">↗</span></article></div>
                <div class="col-md-6 col-xl-3 reveal"><article class="feature-card feature-gold"><div class="feature-icon"><img src="<?= View::escape($asset) ?>/icons/formations.svg" alt="" width="30" height="30"></div><span class="feature-index">03</span><h3>Compare les formations</h3><p>Repère les parcours et les établissements à étudier de plus près.</p><span class="feature-mark" aria-hidden="true">↗</span></article></div>
                <div class="col-md-6 col-xl-3 reveal reveal-delay"><article class="feature-card feature-aqua"><div class="feature-icon"><img src="<?= View::escape($asset) ?>/icons/metiers.svg" alt="" width="30" height="30"></div><span class="feature-index">04</span><h3>Projette-toi dans un métier</h3><p>Relie les études aux débouchés et aux métiers possibles.</p><span class="feature-mark" aria-hidden="true">↗</span></article></div>
            </div>
        </div>
    </section>

    <section class="journey-section section-space" id="parcours" aria-labelledby="journey-title">
        <div class="container"><div class="row align-items-center gy-5">
            <div class="col-lg-5 reveal"><span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Simple, guidé, personnel</span><h2 id="journey-title" class="section-title section-title-light">Un premier pas.<br><em>Un chemin à toi.</em></h2><p class="journey-lead">Prends le temps de comprendre tes envies, puis découvre des pistes concrètes pour la suite de tes études.</p><a class="btn btn-sun" href="#engagement">Voir comment ça marche <span aria-hidden="true">↗</span></a></div>
            <div class="col-lg-7"><div class="journey-steps">
                <article class="journey-step reveal"><div class="journey-step-num">01</div><div class="journey-step-content"><span>POINT DE DÉPART</span><h3>Renseigne ton profil</h3><p>Ta série et quelques informations utiles pour mieux comprendre ta situation.</p></div><span class="journey-step-symbol">◎</span></article>
                <article class="journey-step reveal reveal-delay"><div class="journey-step-num">02</div><div class="journey-step-content"><span>TON EXPLORATION</span><h3>Réponds au test</h3><p>Des questions pour faire émerger ce qui t’intéresse et te correspond.</p></div><span class="journey-step-symbol">✳</span></article>
                <article class="journey-step reveal reveal-delay-2"><div class="journey-step-num">03</div><div class="journey-step-content"><span>TES PISTES</span><h3>Découvre tes résultats</h3><p>Des orientations, des formations, des établissements et des métiers à explorer.</p></div><span class="journey-step-symbol">↗</span></article>
            </div><div class="journey-disclaimer"><span aria-hidden="true">✦</span> Les recommandations sont des pistes pour t’aider à réfléchir à ton projet.</div></div>
        </div></div><div class="journey-watermark" aria-hidden="true">BJ</div>
    </section>

    <section class="promise-section section-space" id="engagement" aria-labelledby="promise-title"><div class="container"><div class="promise-panel reveal">
        <div class="promise-image" role="img" aria-label="Des étudiantes en cours dans une salle de classe à Cotonou"></div>
        <div class="promise-copy"><span class="eyebrow"><span class="eyebrow-dot"></span> À tes côtés pour choisir</span><h2 id="promise-title">Ta prochaine étape mérite un <em>vrai cap.</em></h2><p>ORIENTATION-BJ rassemble des informations utiles pour t’aider à construire une réflexion cohérente avec ta série, tes intérêts et tes ambitions.</p><a class="btn btn-brand" href="#accueil">Revenir en haut <span aria-hidden="true">↑</span></a></div>
        <div class="promise-decoration" aria-hidden="true"><img src="<?= View::escape($asset) ?>/img/symbole-orientation-bj.svg" alt="" width="82" height="82"></div>
    </div></div></section>
</main>

<footer class="site-footer"><div class="container"><div class="footer-main row align-items-center gy-4">
    <div class="col-lg-5"><a href="#accueil" aria-label="ORIENTATION-BJ, accueil"><img class="footer-logo" src="<?= View::escape($asset) ?>/img/logo-orientation-bj-blanc.svg" alt="ORIENTATION-BJ — Ton BAC. Ta voie." width="210" height="52"></a><p class="footer-tagline">Ton BAC. Ta voie.</p></div>
    <div class="col-lg-4"><p class="footer-note">Une plateforme d’orientation académique et professionnelle pensée pour les nouveaux bacheliers au Bénin.</p></div>
    <div class="col-lg-3 footer-links"><a href="#parcours">Le parcours</a><a href="#explorer">Explorer</a><a href="#engagement">Notre engagement</a></div>
</div><div class="footer-bottom"><span>© <?= date('Y') ?> ORIENTATION-BJ</span><span>Construis ton cap, une étape à la fois.</span></div></div></footer>
