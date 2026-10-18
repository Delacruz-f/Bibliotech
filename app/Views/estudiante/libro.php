<?php
declare(strict_types=1);

use App\Core\Url;

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$stock = (int) ($libro['stock_disponible'] ?? 0);
$available = $stock > 0;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#6b2c5f">
    <title><?= $escape($pageTitle) ?> | BiblioTech</title>
    <link rel="stylesheet" href="<?= $escape(Url::to('/assets/css/estudiante.css')) ?>">
</head>
<body>
<?php $activePage = 'catalogo'; require __DIR__ . '/_header.php'; ?>
<main class="student-main">
    <p class="detail-back"><a href="<?= $escape(Url::to('/catalogo')) ?>">← Volver al catálogo</a></p>
    <?php if ($libro === null): ?>
        <section class="empty-state">
            <h1>No encontramos ese libro</h1>
            <p>El libro puede haber sido retirado del catálogo.</p>
            <a class="button inline-button" href="<?= $escape(Url::to('/catalogo')) ?>">Explorar catálogo</a>
        </section>
    <?php else: ?>
        <?php
        $title = (string) ($libro['titulo'] ?? 'Libro sin título');
        $author = (string) ($libro['autor'] ?? 'Autor no especificado');
        $cover = trim((string) ($libro['portada_url'] ?? ''));
        ?>
        <article class="book-detail">
            <div class="book-detail-cover">
                <?php if ($cover !== ''): ?>
                    <img src="<?= $escape($cover) ?>" alt="Portada de <?= $escape($title) ?>">
                <?php else: ?>
                    <span class="cover-placeholder" aria-hidden="true">B</span>
                <?php endif; ?>
            </div>
            <div class="book-detail-content">
                <p class="eyebrow"><?= $escape((string) ($libro['categoria_nombre'] ?? 'Sin categoría')) ?></p>
                <h1><?= $escape($title) ?></h1>
                <p class="book-author">Por <?= $escape($author) ?></p>
                <span class="availability <?= $available ? 'available' : 'unavailable' ?>">
                    <?= $available ? 'Disponible' : 'Agotado' ?>
                </span>
                <?php if (!empty($libro['sinopsis'])): ?>
                    <section class="book-synopsis">
                        <h2>Sinopsis</h2>
                        <p><?= nl2br($escape((string) $libro['sinopsis'])) ?></p>
                    </section>
                <?php endif; ?>
                <dl class="book-metadata">
                    <div><dt>ISBN</dt><dd><?= $escape((string) ($libro['isbn'] ?? '—')) ?></dd></div>
                    <div><dt>Editorial</dt><dd><?= $escape((string) ($libro['editorial'] ?: '—')) ?></dd></div>
                    <div><dt>Año de publicación</dt><dd><?= $escape((string) ($libro['anio'] ?: '—')) ?></dd></div>
                    <div><dt>Ejemplares disponibles</dt><dd><?= $stock ?> de <?= (int) ($libro['stock_total'] ?? 0) ?></dd></div>
                </dl>
                <?php if ($canReserve && $available): ?>
                    <form class="reserve-form detail-reserve" action="<?= $escape(Url::to('/reservas/crear')) ?>" method="post">
                        <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
                        <input type="hidden" name="id_libro" value="<?= (int) $libro['id'] ?>">
                        <button class="button" type="submit">Reservar libro</button>
                    </form>
                <?php elseif (!$canReserve && $available): ?>
                    <p class="reservation-hint">Inicia sesión como estudiante para reservar este libro.</p>
                    <a class="button inline-button" href="<?= $escape(Url::to('/login')) ?>">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>
</main>
</body>
</html>
