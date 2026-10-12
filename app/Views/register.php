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
    <title>Crear cuenta | BiblioTech</title>
    <link rel="stylesheet" href="<?= $escape(Url::to('/assets/css/app.css')) ?>">
</head>
<body>
<main class="auth-shell">
    <section class="auth-card" aria-labelledby="page-title">
        <a class="brand" href="<?= $escape(Url::to('/')) ?>">Biblio<span>Tech</span></a>
        <p class="eyebrow">Un mundo de lecturas te espera</p>
        <h1 id="page-title">Crear una cuenta</h1>
        <p class="muted">Regístrate para explorar el catálogo de BiblioTech.</p>

        <?php if (!empty($error)): ?>
            <p class="notice notice-error" role="alert"><?= $escape((string) $error) ?></p>
        <?php endif; ?>

        <form action="<?= $escape(Url::to('/register')) ?>" method="post">
            <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
            <label for="nombre">Nombre completo</label>
            <input id="nombre" name="nombre" type="text" autocomplete="name" maxlength="150"
                   value="<?= $escape((string) $nombre) ?>" required>

            <label for="email">Correo electrónico</label>
            <input id="email" name="email" type="email" autocomplete="email" maxlength="254"
                   value="<?= $escape((string) $email) ?>" required>

            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" autocomplete="new-password"
                   minlength="8" required>

            <label for="password_confirmation">Confirma tu contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   autocomplete="new-password" minlength="8" required>

            <p class="field-hint">Usa al menos 8 caracteres. Tu cuenta tendrá el rol de estudiante.</p>
            <button class="button" type="submit">Crear cuenta</button>
        </form>
        <p class="form-footer">¿Ya tienes cuenta? <a href="<?= $escape(Url::to('/login')) ?>">Inicia sesión</a></p>
    </section>
</main>
</body>
</html>
