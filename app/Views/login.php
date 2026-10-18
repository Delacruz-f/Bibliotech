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
    <title>Iniciar sesión | BiblioTech</title>
    <link rel="stylesheet" href="<?= $escape(Url::to('/assets/css/app.css')) ?>">
</head>
<body>
<main class="auth-shell">
    <section class="auth-card" aria-labelledby="page-title">
        <a class="brand" href="<?= $escape(Url::to('/')) ?>">Biblio<span>Tech</span></a>
        <p class="eyebrow">Tu biblioteca, en un solo lugar</p>
        <h1 id="page-title">Bienvenido de nuevo</h1>
        <p class="muted">Ingresa tus datos para continuar.</p>

        <?php if (!empty($error)): ?>
            <p class="notice notice-error" role="alert"><?= $escape((string) $error) ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="notice notice-success" role="status"><?= $escape((string) $success) ?></p>
        <?php endif; ?>

        <form action="<?= $escape(Url::to('/login')) ?>" method="post">
            <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
            <label for="email">Correo electrónico</label>
            <input id="email" name="email" type="email" autocomplete="email" maxlength="254"
                   value="<?= $escape((string) $email) ?>" required>

            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>

            <button class="button" type="submit">Iniciar sesión</button>
        </form>
        <p class="form-footer">¿Aún no tienes cuenta? <a href="<?= $escape(Url::to('/register')) ?>">Regístrate</a></p>
        <p class="form-footer">¿Solo quieres explorar? <a href="<?= $escape(Url::to('/catalogo')) ?>">Consulta el catálogo público</a></p>
    </section>
</main>
</body>
</html>
