<?php
use App\Core\View;

$pageTitle = $pageTitle ?? 'Ton BAC. Ta voie.';
$pageDescription = $pageDescription ?? 'ORIENTATION-BJ accompagne les nouveaux bacheliers dans leur orientation académique et professionnelle.';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= View::escape($pageDescription) ?>">
    <meta name="theme-color" content="#24166B">
    <title><?= View::escape($pageTitle) ?> · ORIENTATION-BJ</title>
    <link rel="icon" href="<?= View::escape($basePath) ?>/public/assets/img/favicon.ico" sizes="any">
    <link rel="icon" href="<?= View::escape($basePath) ?>/public/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@1,400;1,500&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= View::escape($basePath) ?>/public/assets/css/app.css">
    <link rel="stylesheet" href="<?= View::escape($basePath) ?>/public/assets/css/visual-fixes.css">
    <link rel="stylesheet" href="<?= View::escape($basePath) ?>/public/assets/css/auth.css">
    <link rel="stylesheet" href="<?= View::escape($basePath) ?>/public/assets/css/module4.css">
    <link rel="stylesheet" href="<?= View::escape($basePath) ?>/public/assets/css/dashboard.css?v=20261112-4">
    <link rel="stylesheet" href="<?= View::escape($basePath) ?>/public/assets/css/favorites.css?v=20261112-1">
    <link rel="stylesheet" href="<?= View::escape($basePath) ?>/public/assets/css/history.css?v=20261112-1">
    <link rel="stylesheet" href="<?= View::escape($basePath) ?>/public/assets/css/admin-dashboard.css?v=20261112-1">
    <link rel="stylesheet" href="<?= View::escape($basePath) ?>/public/assets/css/admin-security.css?v=20261112-1">
</head>
<body>
    <?= $content ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="<?= View::escape($basePath) ?>/public/assets/js/app.js" defer></script>
</body>
</html>

