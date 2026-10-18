<?php
declare(strict_types=1);

use App\Core\View;

/** @var list<array<string, mixed>> $filas */
/** @var array{total: int, pendientes: int, pagadas: int, monto_pendiente: float} $resumen */
/** @var App\Core\Paginator $pag */
/** @var string $q */
/** @var string $estado */
/** @var list<string> $estados */
/** @var string $csrf */
?>
<section class="librarian-page-heading">
    <div>
        <p class="librarian-eyebrow">Circulación</p>
        <h1>Multas</h1>
        <p>Pagos y multas generadas por devolución tardía.</p>
    </div>
</section>

<section class="summary-grid" aria-label="Resumen de multas">
    <article><span>Total</span><strong><?= $resumen['total'] ?></strong></article>
    <article><span>Pendientes</span><strong class="value-red"><?= $resumen['pendientes'] ?></strong></article>
    <article><span>Pagadas</span><strong class="value-green"><?= $resumen['pagadas'] ?></strong></article>
    <article><span>Monto pendiente</span><strong class="value-gold"><?= View::soles($resumen['monto_pendiente']) ?></strong></article>
</section>

<form class="librarian-filters" method="get" action="<?= View::url('/bibliotecario/multas') ?>">
    <label class="sr-only" for="fine-search">Buscar multas</label>
    <input id="fine-search" type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar por libro o estudiante" maxlength="100">
    <label class="sr-only" for="fine-state">Filtrar por estado</label>
    <select id="fine-state" name="estado">
        <option value="">Todos los estados</option>
        <?php foreach ($estados as $opcion): ?>
            <option value="<?= View::e($opcion) ?>"<?= $opcion === $estado ? ' selected' : '' ?>><?= View::e(ucfirst($opcion)) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="librarian-button button-secondary" type="submit">Filtrar</button>
</form>

<div class="librarian-table-wrap">
    <table class="librarian-table">
        <thead><tr><th>Estudiante</th><th>Libro</th><th>Monto</th><th>Generada</th><th>Estado</th><th>Acción</th></tr></thead>
        <tbody>
        <?php if ($filas === []): ?>
            <tr><td colspan="6" class="table-empty">No hay multas para mostrar.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $fila): ?>
            <tr>
                <td><?= View::e($fila['usuario']) ?><small><?= View::e($fila['email']) ?></small></td>
                <td><?= View::e($fila['libro']) ?></td>
                <td><strong><?= View::soles($fila['monto']) ?></strong></td>
                <td><?= View::fecha($fila['fecha_generacion'], true) ?></td>
                <td><span class="librarian-badge status-<?= View::e($fila['estado']) ?>"><?= View::e(ucfirst($fila['estado'])) ?></span>
                    <?php if ($fila['fecha_pago'] !== null): ?><small><?= View::fecha($fila['fecha_pago'], true) ?></small><?php endif; ?></td>
                <td class="table-actions">
                    <?php if ($fila['estado'] === 'pendiente'): ?>
                        <form method="post" action="<?= View::url('/bibliotecario/multas/pagar') ?>">
                            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
                            <button class="text-button" type="submit">Registrar pago</button>
                        </form>
                    <?php else: ?>
                        <span class="muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= View::paginacion($pag, '/bibliotecario/multas') ?>
