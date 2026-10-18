<?php
declare(strict_types=1);

use App\Core\Url;

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$libros = $pageData['libros'];
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
    <section class="page-intro">
        <p class="eyebrow">Explora y descubre</p>
        <h1>Catálogo de libros</h1>
        <p>Encuentra tu próxima lectura entre nuestros títulos disponibles.</p>
    </section>

    <?php if (!empty($flash['success'])): ?>
        <p class="alert alert-success" role="status"><?= $escape($flash['success']) ?></p>
    <?php endif; ?>
    <?php if (!empty($flash['error'])): ?>
        <p class="alert alert-error" role="alert"><?= $escape($flash['error']) ?></p>
    <?php endif; ?>

    <?php if ($libros === []): ?>
        <div class="empty-state">
            <h2>Aún no hay libros en el catálogo</h2>
            <p>Vuelve pronto para descubrir nuevas lecturas.</p>
        </div>
    <?php else: ?>
        <section class="book-grid" aria-label="Libros del catálogo">
            <?php foreach ($libros as $libro): ?>
                <?php
                $bookId = (int) ($libro['id'] ?? 0);
                $stock = (int) ($libro['stock_disponible'] ?? 0);
                $available = $stock > 0;
                $title = (string) ($libro['titulo'] ?? 'Libro sin título');
                $author = (string) ($libro['autor'] ?? 'Autor no especificado');
                $category = (string) ($libro['categoria_nombre'] ?? 'Sin categoría');
                $cover = trim((string) ($libro['portada_url'] ?? ''));
                $detailsUrl = Url::to('/catalogo/show?id=' . $bookId);
                ?>
                <article class="book-card" id="libro-<?= $bookId ?>">
                    <div class="book-cover">
                        <?php if ($cover !== ''): ?>
                            <img src="<?= $escape($cover) ?>" alt="Portada de <?= $escape($title) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="cover-placeholder" aria-hidden="true">B</span>
                        <?php endif; ?>
                    </div>
                    <div class="book-content">
                        <span class="availability <?= $available ? 'available' : 'unavailable' ?>">
                            <?= $available ? 'Disponible' : 'Agotado' ?>
                        </span>
                        <h2><?= $escape($title) ?></h2>
                        <p class="book-author"><?= $escape($author) ?></p>
                        <p class="book-category"><?= $escape($category) ?></p>
                        <a class="book-details" href="<?= $escape($detailsUrl) ?>">Ver libro <span aria-hidden="true">→</span></a>
                        <?php if ($canReserve && $available): ?>
                            <form class="reserve-form" action="<?= $escape(Url::to('/reservas/crear')) ?>" method="post">
                                <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
                                <input type="hidden" name="id_libro" value="<?= $bookId ?>">
                                <button class="button button-small" type="submit">Reservar libro</button>
                            </form>
                        <?php elseif (!$canReserve): ?>
                            <p class="reservation-hint">Inicia sesión como estudiante para reservar.</p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <?php if ($pageData['totalPaginas'] > 1): ?>
            <nav class="pagination" aria-label="Paginación del catálogo">
                <?php if ($pageData['pagina'] > 1): ?>
                    <a class="page-link" href="<?= $escape(Url::to('/catalogo?pagina=' . ($pageData['pagina'] - 1))) ?>">Anterior</a>
                <?php else: ?>
                    <span class="page-link disabled" aria-disabled="true">Anterior</span>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $pageData['totalPaginas']; $page++): ?>
                    <?php if (abs($page - $pageData['pagina']) <= 2 || $page === 1 || $page === $pageData['totalPaginas']): ?>
                        <a class="page-number <?= $page === $pageData['pagina'] ? 'current' : '' ?>"
                           href="<?= $escape(Url::to('/catalogo?pagina=' . $page)) ?>"
                           <?= $page === $pageData['pagina'] ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                    <?php elseif (abs($page - $pageData['pagina']) === 3): ?>
                        <span class="page-ellipsis" aria-hidden="true">…</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pageData['pagina'] < $pageData['totalPaginas']): ?>
                    <a class="page-link" href="<?= $escape(Url::to('/catalogo?pagina=' . ($pageData['pagina'] + 1))) ?>">Siguiente</a>
                <?php else: ?>
                    <span class="page-link disabled" aria-disabled="true">Siguiente</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>
