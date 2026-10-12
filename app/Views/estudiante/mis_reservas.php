<?php
declare(strict_types=1);

use App\Core\Url;

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$stateLabels = [
    'pendiente' => 'Pendiente',
    'confirmada' => 'Confirmada',
    'expirada' => 'Expirada',
    'cancelada' => 'Cancelada',
];
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
<?php $activePage = 'reservas'; require __DIR__ . '/_header.php'; ?>
<main class="student-main">
    <section class="page-intro">
        <p class="eyebrow">Tus lecturas apartadas</p>
        <h1>Mis reservas</h1>
        <p>Consulta el estado y las fechas de tus reservas de libros.</p>
    </section>

    <?php foreach (['success' => 'alert-success', 'error' => 'alert-error'] as $key => $class): ?>
        <?php if (!empty($flash[$key])): ?>
            <p class="alert <?= $class ?>" role="<?= $key === 'error' ? 'alert' : 'status' ?>"><?= $escape($flash[$key]) ?></p>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($reservas === []): ?>
        <div class="empty-state">
            <h2>No tienes reservas todavía</h2>
            <p>Explora el catálogo y reserva un libro para verlo aquí.</p>
            <a class="button inline-button" href="<?= $escape(Url::to('/catalogo')) ?>">Ir al catálogo</a>
        </div>
    <?php else: ?>
        <section class="reservation-list" aria-label="Lista de reservas">
            <?php foreach ($reservas as $reserva): ?>
                <?php
                $state = strtolower((string) ($reserva['estado'] ?? ''));
                $stateLabel = $stateLabels[$state] ?? ucfirst($state ?: 'Desconocido');
                ?>
                <article class="reservation-card">
                    <div class="reservation-info">
                        <span class="status-badge status-<?= $escape($state) ?>"><?= $escape($stateLabel) ?></span>
                        <h2><?= $escape((string) ($reserva['libro_titulo'] ?? 'Libro no disponible')) ?></h2>
                        <p class="book-author"><?= $escape((string) ($reserva['libro_autor'] ?? '')) ?></p>
                        <dl class="reservation-dates">
                            <div>
                                <dt>Fecha de reserva</dt>
                                <dd><?= $escape((string) ($reserva['fecha_reserva'] ?? '—')) ?></dd>
                            </div>
                            <div>
                                <dt>Fecha de expiración</dt>
                                <dd><?= $escape((string) ($reserva['fecha_expiracion'] ?? '—')) ?></dd>
                            </div>
                        </dl>
                    </div>
                    <div class="reservation-action">
                        <?php if ($state === 'pendiente'): ?>
                            <form action="<?= $escape(Url::to('/reservas/cancelar')) ?>" method="post">
                                <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
                                <input type="hidden" name="id_reserva" value="<?= (int) ($reserva['id'] ?? 0) ?>">
                                <button class="button button-danger" type="submit">Cancelar reserva</button>
                            </form>
                        <?php elseif ($state === 'confirmada'): ?>
                            <p class="pickup-message">Listo para retirar</p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
