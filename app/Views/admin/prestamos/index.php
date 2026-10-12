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
<div class="adm-head">
    <div>
        <h1 class="adm-title">Préstamos</h1>
        <p class="adm-subtitle"><?= $pag->total ?> préstamo(s) · activos e históricos</p>
    </div>
    <a class="adm-btn" href="<?= View::url('/admin/prestamos/crear') ?>">+ Registrar préstamo</a>
</div>

<form class="adm-search" method="get" action="<?= View::url('/admin/prestamos') ?>">
    <input type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar por libro o usuario" maxlength="100" aria-label="Buscar préstamos">
    <select name="estado" aria-label="Filtrar por estado">
        <option value="">Todos los estados</option>
        <?php foreach ($estados as $e): ?>
            <option value="<?= View::e($e) ?>"<?= $e === $estado ? ' selected' : '' ?>><?= View::e(ucfirst($e)) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="adm-btn adm-btn-ghost" type="submit">Filtrar</button>
</form>

<div class="adm-table-wrap">
    <table class="adm-table">
        <thead>
        <tr><th>Libro</th><th>Usuario</th><th>Fecha de préstamo</th><th>Devolución</th><th>Estado</th><th class="right">Acciones</th></tr>
        </thead>
        <tbody>
        <?php if ($filas === []): ?>
            <tr><td colspan="6" class="adm-empty">No hay préstamos para mostrar.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $f): ?>
            <tr>
                <td><strong><?= View::e($f['libro']) ?></strong></td>
                <td><?= View::e($f['usuario']) ?><small class="adm-sub"><?= View::e($f['email']) ?></small></td>
                <td><?= View::fecha($f['fecha_prestamo']) ?></td>
                <td>
                    <?php if ($f['fecha_devolucion_real'] !== null): ?>
                        <?= View::fecha($f['fecha_devolucion_real']) ?><small class="adm-sub">Entregado</small>
                    <?php else: ?>
                        <?= View::fecha($f['fecha_devolucion_esperada']) ?><small class="adm-sub">Esperada</small>
                    <?php endif; ?>
                </td>
                <td><?= View::badge((string) $f['estado']) ?><?= (int) $f['tiene_multa'] > 0 ? ' <small class="adm-sub">con multa</small>' : '' ?></td>
                <td class="right actions">
                    <?php if (in_array($f['estado'], ['activo', 'vencido'], true)): ?>
                        <form method="post" action="<?= View::url('/admin/prestamos/devolver') ?>" data-confirm="¿Registrar la devolución de «<?= View::e($f['libro']) ?>»?">
                            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                            <button class="adm-link" type="submit">Registrar devolución</button>
                        </form>
                    <?php else: ?>
                        <span class="adm-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= View::paginacion($pag, '/admin/prestamos') ?>
