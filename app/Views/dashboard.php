<?php
declare(strict_types=1);

use App\Core\Url;

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($pageTitle) ?> | BiblioTech</title>
    <link rel="stylesheet" href="<?= $escape(Url::to('/assets/css/app.css')) ?>">
</head>
<body>
<main class="auth-shell">
    <section class="auth-card dashboard-card">
        <a class="brand" href="<?= $escape(Url::to('/')) ?>">Biblio<span>Tech</span></a>
        <p class="eyebrow"><?= $escape($pageTitle) ?></p>
        <h1>Hola, <?= $escape((string) ($_SESSION['user']['nombre'] ?? '')) ?></h1>
        <p class="muted">Has iniciado sesión como <?= $escape((string) ($_SESSION['user']['rol'] ?? '')) ?>.</p>
        <p>La sesión y el acceso a esta sección están protegidos según tu rol.</p>
        <form action="<?= $escape(Url::to('/logout')) ?>" method="post">
            <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
            <button class="button button-secondary" type="submit">Cerrar sesión</button>
        </form>
    </section>
</main>
</body>
</html>
