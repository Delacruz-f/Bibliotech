<?php
declare(strict_types=1);

use App\Core\View;

/** @var list<array<string, mixed>> $filas */
/** @var App\Core\Paginator $pag */
/** @var string $q */
/** @var string $estado */
/** @var list<string> $estados */
/** @var string $csrf */
?>
<section class="librarian-page-heading">
    <div>
        <p class="librarian-eyebrow">Circulación</p>
        <h1>Préstamos</h1>
        <p><?= $pag->total ?> registro(s), incluyendo el historial.</p>
    </div>
    <a class="librarian-button" href="<?= View::url('/bibliotecario/prestamos/crear') ?>">+ Registrar préstamo</a>
</section>

<form class="librarian-filters" method="get" action="<?= View::url('/bibliotecario/prestamos') ?>">
    <label class="sr-only" for="loan-search">Buscar préstamos</label>
    <input id="loan-search" type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar por libro o usuario" maxlength="100">
    <label class="sr-only" for="loan-state">Filtrar por estado</label>
    <select id="loan-state" name="estado">
        <option value="">Todos los estados</option>
        <?php foreach ($estados as $opcion): ?>
            <option value="<?= View::e($opcion) ?>"<?= $opcion === $estado ? ' selected' : '' ?>><?= View::e(ucfirst($opcion)) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="librarian-button button-secondary" type="submit">Filtrar</button>
</form>

<div class="librarian-table-wrap">
    <table class="librarian-table">
        <thead><tr><th>Libro</th><th>Estudiante</th><th>Préstamo</th><th>Devolución</th><th>Estado</th><th>Acción</th></tr></thead>
        <tbody>
        <?php if ($filas === []): ?>
            <tr><td colspan="6" class="table-empty">No hay préstamos para mostrar.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $fila): ?>
            <tr>
                <td><strong><?= View::e($fila['libro']) ?></strong></td>
                <td><?= View::e($fila['usuario']) ?><small><?= View::e($fila['email']) ?></small></td>
                <td><?= View::fecha($fila['fecha_prestamo']) ?></td>
                <td><?= View::fecha($fila['fecha_devolucion_real'] ?? $fila['fecha_devolucion_esperada']) ?>
                    <small><?= $fila['fecha_devolucion_real'] !== null ? 'Real' : 'Esperada' ?></small></td>
                <td><span class="librarian-badge status-<?= View::e($fila['estado']) ?>"><?= View::e(ucfirst($fila['estado'])) ?></span>
                    <?php if ((int) $fila['tiene_multa'] > 0): ?><small class="table-note">Con multa</small><?php endif; ?></td>
                <td class="table-actions">
                    <?php if (in_array($fila['estado'], ['activo', 'vencido'], true)): ?>
                        <form method="post" action="<?= View::url('/bibliotecario/prestamos/devolver') ?>">
                            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
                            <button class="text-button" type="submit">Registrar devolución</button>
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
<?= View::paginacion($pag, '/bibliotecario/prestamos') ?>
