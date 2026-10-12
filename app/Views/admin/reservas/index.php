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
        <h1 class="adm-title"><?= $estado === 'pendiente' ? 'Reservas activas' : 'Reservas' ?></h1>
        <p class="adm-subtitle"><?= $pag->total ?> reserva(s) · confirma la entrega para generar el préstamo</p>
    </div>
</div>

<form class="adm-search" method="get" action="<?= View::url('/admin/reservas') ?>">
    <input type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar por libro o usuario" maxlength="100" aria-label="Buscar reservas">
    <select name="estado" aria-label="Filtrar por estado">
        <option value="">Todos los estados</option>
        <?php foreach ($estados as $e): ?>
            <option value="<?= View::e($e) ?>"<?= $e === $estado ? ' selected' : '' ?>><?= View::e($e === 'pendiente' ? 'Pendientes (activas)' : ucfirst($e)) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="adm-btn adm-btn-ghost" type="submit">Filtrar</button>
</form>

<div class="adm-table-wrap">
    <table class="adm-table">
        <thead>
        <tr><th>Libro</th><th>Usuario</th><th>Reservado</th><th>Expira</th><th>Estado</th><th class="right">Acciones</th></tr>
        </thead>
        <tbody>
        <?php if ($filas === []): ?>
            <tr><td colspan="6" class="adm-empty">No hay reservas para mostrar.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $f): ?>
            <tr>
                <td><strong><?= View::e($f['libro']) ?></strong><small class="adm-sub">Disponibles: <?= (int) $f['stock_disponible'] ?></small></td>
                <td><?= View::e($f['usuario']) ?><small class="adm-sub"><?= View::e($f['email']) ?></small></td>
                <td><?= View::fecha($f['fecha_reserva'], true) ?></td>
                <td><?= View::fecha($f['fecha_expiracion'], true) ?></td>
                <td><?= View::badge((string) $f['estado']) ?></td>
                <td class="right actions">
                    <?php if ($f['estado'] === 'pendiente'): ?>
                        <form method="post" action="<?= View::url('/admin/reservas/confirmar') ?>" data-confirm="¿Confirmar la entrega de «<?= View::e($f['libro']) ?>» a <?= View::e($f['usuario']) ?>?">
                            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                            <button class="adm-link" type="submit">Confirmar entrega</button>
                        </form>
                        <form method="post" action="<?= View::url('/admin/reservas/cancelar') ?>" data-confirm="¿Cancelar esta reserva?">
                            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                            <button class="adm-link adm-danger" type="submit">Cancelar</button>
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
<?= View::paginacion($pag, '/admin/reservas') ?>
