<?php
declare(strict_types=1);

use App\Core\Url;

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$userName = is_array($_SESSION['user'] ?? null)
    ? (string) ($_SESSION['user']['nombre'] ?? '')
    : '';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#6b2c5f">
    <title>Inicio | BiblioTech</title>
    <link rel="stylesheet" href="<?= $escape(Url::to('/assets/css/home.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= $escape(Url::to('/home')) ?>" aria-label="BiblioTech, inicio">BiblioTech</a>

        <nav class="main-nav" aria-label="Navegación principal">
            <a class="nav-link active" href="<?= $escape(Url::to('/home')) ?>" aria-current="page">Inicio</a>
            <?php if (($_SESSION['user']['rol'] ?? '') === 'estudiante'): ?>
                <a class="nav-link" href="<?= $escape(Url::to('/catalogo')) ?>">Catálogo</a>
                <a class="nav-link" href="<?= $escape(Url::to('/mis-reservas')) ?>">Mis reservas</a>
                <a class="nav-link" href="<?= $escape(Url::to('/mis-multas')) ?>">Mis multas</a>
            <?php endif; ?>
            <?php if (($_SESSION['user']['rol'] ?? '') === 'administrador'): ?>
                <a class="nav-link" href="<?= $escape(Url::to('/admin')) ?>">Admin</a>
            <?php endif; ?>
        </nav>

        <div class="account-actions">
            <span class="user-name"><?= $escape($userName) ?></span>
            <form action="<?= $escape(Url::to('/logout')) ?>" method="post">
                <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
                <button class="logout-button" type="submit">Salir</button>
            </form>
        </div>
    </div>
</header>

<main>
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-content">
            <p class="hero-kicker">Tu biblioteca, más cerca</p>
            <h1 id="hero-title">Plataforma <span>BiblioTech</span></h1>
            <p class="hero-subtitle">Solución tecnológica desarrollada sobre entorno LAMP.</p>
            <a class="primary-button" href="<?= $escape(Url::to('/catalogo')) ?>">Comenzar Ahora</a>
        </div>
        <div class="hero-decoration" aria-hidden="true">
            <span class="decoration-orbit orbit-one"></span>
            <span class="decoration-orbit orbit-two"></span>
            <span class="decoration-book">BT</span>
        </div>
    </section>

    <section class="features" id="catalogo" aria-labelledby="features-title">
        <div class="section-heading">
            <p class="section-kicker">Todo lo que necesitas</p>
            <h2 id="features-title">Características</h2>
            <span class="heading-underline" aria-hidden="true"></span>
        </div>

        <div class="feature-grid">
            <article class="feature-card" id="reservas">
                <span class="feature-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 4.75A2.75 2.75 0 0 1 7.75 2H20v17H7.75A2.75 2.75 0 0 0 5 21.75v-17Z" />
                        <path d="M5 5v16.75M9 6h7M9 10h7" />
                    </svg>
                </span>
                <h3>Reserva online</h3>
                <p>Reserva tus libros desde cualquier dispositivo.</p>
            </article>

            <article class="feature-card">
                <span class="feature-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 20V8l8-4 8 4v12H4Z" />
                        <path d="M8 20v-7h8v7M8 9h.01M12 9h.01M16 9h.01" />
                    </svg>
                </span>
                <h3>Control de stock</h3>
                <p>Consulta disponibilidad en tiempo real.</p>
            </article>

            <article class="feature-card" id="multas">
                <span class="feature-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3.25 2M8.5 2.75 7 4M15.5 2.75 17 4" />
                    </svg>
                </span>
                <h3>Gestión de multas</h3>
                <p>Cálculo automático de moras por retraso.</p>
            </article>
        </div>
    </section>
</main>

<footer class="site-footer">
    <p>© <?= date('Y') ?> BiblioTech <span aria-hidden="true">·</span> Un espacio para cada historia.</p>
</footer>
</body>
</html>
