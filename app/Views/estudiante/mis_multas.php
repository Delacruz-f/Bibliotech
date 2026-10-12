<?php
declare(strict_types=1);

use App\Core\Url;

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$formatMoney = static fn (mixed $amount): string => '$' . number_format((float) $amount, 2, '.', ',');
$isPaid = static fn (mixed $status): bool => strtolower(trim((string) $status)) === 'pagada';
$isPending = static fn (mixed $status): bool => strtolower(trim((string) $status)) === 'pendiente';
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
<?php $activePage = 'multas'; require __DIR__ . '/_header.php'; ?>
<main class="student-main">
    <section class="page-intro">
        <p class="eyebrow">Estado de cuenta</p>
        <h1>Mis multas</h1>
        <p>Consulta tus cargos por retraso y el estado de cada multa.</p>
    </section>

    <?php foreach (['success' => 'alert-success', 'error' => 'alert-error', 'info' => 'alert-info'] as $key => $class): ?>
        <?php if (!empty($flash[$key])): ?>
            <p class="alert <?= $class ?>" role="<?= $key === 'error' ? 'alert' : 'status' ?>"><?= $escape($flash[$key]) ?></p>
        <?php endif; ?>
    <?php endforeach; ?>

    <section class="summary-grid" aria-label="Resumen de multas">
        <article class="summary-card">
            <span class="summary-label">Total</span>
            <strong><?= (int) $summary['total'] ?></strong>
            <span class="summary-mark mark-purple" aria-hidden="true">#</span>
        </article>
        <article class="summary-card">
            <span class="summary-label">Pendientes</span>
            <strong><?= (int) $summary['pendientes'] ?></strong>
            <span class="summary-mark mark-amber" aria-hidden="true">!</span>
        </article>
        <article class="summary-card">
            <span class="summary-label">Pagadas</span>
            <strong><?= (int) $summary['pagadas'] ?></strong>
            <span class="summary-mark mark-green" aria-hidden="true">✓</span>
        </article>
        <article class="summary-card">
            <span class="summary-label">Monto pendiente</span>
            <strong class="summary-amount"><?= $escape($formatMoney($summary['monto_pendiente'])) ?></strong>
            <span class="summary-mark mark-red" aria-hidden="true">$</span>
        </article>
    </section>

    <section class="table-panel" aria-labelledby="fines-heading">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Detalle</p>
                <h2 id="fines-heading">Historial de multas</h2>
            </div>
        </div>
        <?php if ($multas === []): ?>
            <div class="empty-state compact">
                <h3>¡Todo en orden!</h3>
                <p>No tienes multas registradas.</p>
            </div>
        <?php else: ?>
            <div class="table-scroll">
                <table class="fines-table">
                    <thead>
                    <tr>
                        <th scope="col">Libro</th>
                        <th scope="col">Monto</th>
                        <th scope="col">Fecha generada</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($multas as $multa): ?>
                        <?php $paid = $isPaid($multa['estado'] ?? ''); ?>
                        <tr>
                            <td data-label="Libro"><?= $escape((string) ($multa['libro_titulo'] ?? 'Préstamo')) ?></td>
                            <td data-label="Monto" class="amount-cell"><?= $escape($formatMoney($multa['monto'] ?? 0)) ?></td>
                            <td data-label="Fecha generada"><?= $escape((string) ($multa['fecha_generacion'] ?? '—')) ?></td>
                            <td data-label="Estado">
                                <span class="status-badge <?= $paid ? 'status-paid' : ($isPending($multa['estado'] ?? '') ? 'status-pending' : 'status-condoned') ?>">
                                    <?= $paid ? 'Pagada' : ($isPending($multa['estado'] ?? '') ? 'Pendiente' : 'Condonada') ?>
                                </span>
                            </td>
                            <td data-label="Acciones">
                                <?php if ($paid): ?>
                                    <small class="paid-date">
                                        <?= !empty($multa['fecha_pago'])
                                            ? 'Pagada el ' . $escape((string) $multa['fecha_pago'])
                                            : 'Pago registrado' ?>
                                    </small>
                                <?php elseif ($isPending($multa['estado'] ?? '')): ?>
                                    <form action="<?= $escape(Url::to('/mis-multas/pagar')) ?>" method="post">
                                        <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">
                                        <input type="hidden" name="id_multa" value="<?= (int) ($multa['id'] ?? 0) ?>">
                                        <button class="button button-small" type="submit">Pagar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
