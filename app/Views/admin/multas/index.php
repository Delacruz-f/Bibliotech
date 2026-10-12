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
<div class="adm-head">
    <div>
        <h1 class="adm-title">Multas</h1>
        <p class="adm-subtitle">Se generan al devolver con atraso: S/ 9.00 por cada 6 días (S/ 1.50 por día)</p>
    </div>
</div>

<section class="adm-cards adm-cards-4" aria-label="Resumen de multas">
    <article class="adm-card"><span class="adm-card-label">Total</span><strong class="adm-card-value"><?= $resumen['total'] ?></strong></article>
    <article class="adm-card"><span class="adm-card-label">Pendientes</span><strong class="adm-card-value"><?= $resumen['pendientes'] ?></strong></article>
    <article class="adm-card"><span class="adm-card-label">Pagadas</span><strong class="adm-card-value adm-green"><?= $resumen['pagadas'] ?></strong></article>
    <article class="adm-card"><span class="adm-card-label">Monto pendiente</span><strong class="adm-card-value adm-gold"><?= View::soles($resumen['monto_pendiente']) ?></strong></article>
</section>

<form class="adm-search" method="get" action="<?= View::url('/admin/multas') ?>">
    <input type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar por libro o usuario" maxlength="100" aria-label="Buscar multas">
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
        <tr><th>Usuario</th><th>Libro</th><th>Monto</th><th>Fecha de generación</th><th>Estado</th><th class="right">Acciones</th></tr>
        </thead>
        <tbody>
        <?php if ($filas === []): ?>
            <tr><td colspan="6" class="adm-empty">No hay multas para mostrar.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $f): ?>
            <tr>
                <td><strong><?= View::e($f['usuario']) ?></strong><small class="adm-sub"><?= View::e($f['email']) ?></small></td>
                <td><?= View::e($f['libro']) ?></td>
                <td><strong><?= View::soles($f['monto']) ?></strong></td>
                <td><?= View::fecha($f['fecha_generacion'], true) ?></td>
                <td><?= View::badge((string) $f['estado']) ?><?= $f['fecha_pago'] !== null ? '<small class="adm-sub">' . View::fecha($f['fecha_pago']) . '</small>' : '' ?></td>
                <td class="right actions">
                    <?php if ($f['estado'] === 'pendiente'): ?>
                        <form method="post" action="<?= View::url('/admin/multas/pagar') ?>" data-confirm="¿Registrar el pago de <?= View::e(View::soles($f['monto'])) ?> de <?= View::e($f['usuario']) ?>?">
                            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                            <button class="adm-link" type="submit">Registrar pago</button>
                        </form>
                        <form method="post" action="<?= View::url('/admin/multas/condonar') ?>" data-confirm="¿Condonar esta multa? No se cobrará.">
                            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                            <button class="adm-link adm-danger" type="submit">Condonar</button>
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
<?= View::paginacion($pag, '/admin/multas') ?>
