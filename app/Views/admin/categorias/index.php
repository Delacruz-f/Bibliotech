<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\View;

/** @var list<array<string, mixed>> $filas */
/** @var App\Core\Paginator $pag */
/** @var string $q */
/** @var string $csrf */
?>
<div class="adm-head">
    <div>
        <h1 class="adm-title">Categorías</h1>
        <p class="adm-subtitle"><?= $pag->total ?> categoría(s)</p>
    </div>
    <a class="adm-btn" href="<?= View::url('/admin/categorias/crear') ?>">+ Nueva categoría</a>
</div>

<form class="adm-search" method="get" action="<?= View::url('/admin/categorias') ?>">
    <input type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar categoría" maxlength="100" aria-label="Buscar categorías">
    <button class="adm-btn adm-btn-ghost" type="submit">Buscar</button>
</form>

<div class="adm-table-wrap">
    <table class="adm-table">
        <thead><tr><th>Nombre</th><th>Descripción</th><th>Libros</th><th class="right">Acciones</th></tr></thead>
        <tbody>
        <?php if ($filas === []): ?>
            <tr><td colspan="4" class="adm-empty">No se encontraron categorías.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $f): ?>
            <tr>
                <td><strong><?= View::e($f['nombre']) ?></strong></td>
                <td><?= View::e(mb_strimwidth((string) ($f['descripcion'] ?? ''), 0, 90, '…') ?: '—') ?></td>
                <td><?= (int) $f['total_libros'] ?></td>
                <td class="right actions">
                    <a class="adm-link" href="<?= View::e(Url::to('/admin/categorias/editar') . '?id=' . (int) $f['id']) ?>">Editar</a>
                    <form method="post" action="<?= View::url('/admin/categorias/eliminar') ?>" data-confirm="¿Eliminar la categoría «<?= View::e($f['nombre']) ?>»?">
                        <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                        <button class="adm-link adm-danger" type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= View::paginacion($pag, '/admin/categorias') ?>
