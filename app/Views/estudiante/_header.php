<?php
declare(strict_types=1);

use App\Core\Url;

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$userName = is_array($_SESSION['user'] ?? null)
    ? (string) ($_SESSION['user']['nombre'] ?? '')
    : '';
?>
<header class="student-header">
    <div class="student-header-inner">
        <a class="student-brand" href="<?= $escape(Url::to('/home')) ?>">BiblioTech</a>
        <nav class="student-nav" aria-label="Navegación de estudiante">
            <a class="<?= $activePage === 'catalogo' ? 'active' : '' ?>" href="<?= $escape(Url::to('/catalogo')) ?>">Catálogo</a>
            <a class="<?= $activePage === 'reservas' ? 'active' : '' ?>" href="<?= $escape(Url::to('/mis-reservas')) ?>">Mis reservas</a>
            <a class="<?= $activePage === 'multas' ? 'active' : '' ?>" href="<?= $escape(Url::to('/mis-multas')) ?>">Mis multas</a>
        </nav>
        <div class="student-account">
            <span class="student-user-name"><?= $escape($userName) ?></span>
            <form action="<?= $escape(Url::to('/logout')) ?>" method="post">
                <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
                <button class="student-logout" type="submit">Salir</button>
            </form>
        </div>
    </div>
</header>
