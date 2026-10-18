<?php
declare(strict_types=1);

use App\Core\Url;

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$userName = is_array($_SESSION['user'] ?? null)
    ? (string) ($_SESSION['user']['nombre'] ?? '')
    : '';
$userRole = is_array($_SESSION['user'] ?? null)
    ? (string) ($_SESSION['user']['rol'] ?? '')
    : '';
?>
<header class="student-header">
    <div class="student-header-inner">
        <a class="student-brand" href="<?= $escape(Url::to($userRole === 'estudiante' ? '/home' : '/catalogo')) ?>">BiblioTech</a>
        <nav class="student-nav" aria-label="Navegación principal">
            <a class="<?= $activePage === 'catalogo' ? 'active' : '' ?>" href="<?= $escape(Url::to('/catalogo')) ?>">Catálogo</a>
            <?php if ($userRole === 'estudiante'): ?>
                <a class="<?= $activePage === 'reservas' ? 'active' : '' ?>" href="<?= $escape(Url::to('/mis-reservas')) ?>">Mis reservas</a>
                <a class="<?= $activePage === 'multas' ? 'active' : '' ?>" href="<?= $escape(Url::to('/mis-multas')) ?>">Mis multas</a>
            <?php endif; ?>
        </nav>
        <div class="student-account">
            <?php if ($userRole !== ''): ?>
                <span class="student-user-name"><?= $escape($userName) ?></span>
                <form action="<?= $escape(Url::to('/logout')) ?>" method="post">
                    <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
                    <button class="student-logout" type="submit">Salir</button>
                </form>
            <?php else: ?>
                <a class="student-user-name" href="<?= $escape(Url::to('/login')) ?>">Iniciar sesión</a>
                <a class="student-logout" href="<?= $escape(Url::to('/register')) ?>">Crear cuenta</a>
            <?php endif; ?>
        </div>
    </div>
</header>
