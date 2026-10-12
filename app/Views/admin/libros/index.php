<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\View;

/** @var list<array<string, mixed>> $filas */
/** @var list<array<string, mixed>> $categorias */
/** @var App\Core\Paginator $pag */
/** @var string $q */
/** @var int $cat */
/** @var string $csrf */
?>
<div class="adm-head">
    <div>
        <h1 class="adm-title">Libros</h1>
        <p class="adm-subtitle"><?= $pag->total ?> libro(s) en el catálogo</p>
    </div>
    <a class="adm-btn" href="<?= View::url('/admin/libros/crear') ?>">+ Nuevo libro</a>
</div>

<form class="adm-search" method="get" action="<?= View::url('/admin/libros') ?>">
    <input type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar por título, autor o ISBN" maxlength="100" aria-label="Buscar libros">
    <select name="categoria" aria-label="Filtrar por categoría">
        <option value="0">Todas las categorías</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= (int) $c['id'] ?>"<?= (int) $c['id'] === $cat ? ' selected' : '' ?>><?= View::e($c['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="adm-btn adm-btn-ghost" type="submit">Buscar</button>
</form>

<div class="adm-table-wrap">
    <table class="adm-table">
        <thead>
        <tr><th>Título</th><th>Autor</th><th>ISBN</th><th>Categoría</th><th>Stock</th><th class="right">Acciones</th></tr>
        </thead>
        <tbody>
        <?php if ($filas === []): ?>
            <tr><td colspan="6" class="adm-empty">No se encontraron libros.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $f): ?>
            <tr>
                <td><strong><?= View::e($f['titulo']) ?></strong></td>
                <td><?= View::e($f['autor']) ?></td>
                <td><?= View::e($f['isbn']) ?></td>
                <td><?= View::e($f['categoria'] ?? '—') ?></td>
                <td><span class="adm-stock<?= (int) $f['stock_disponible'] === 0 ? ' adm-stock-zero' : '' ?>"><?= (int) $f['stock_disponible'] ?>/<?= (int) $f['stock_total'] ?></span></td>
                <td class="right actions">
                    <a class="adm-link" href="<?= View::e(Url::to('/admin/libros/editar') . '?id=' . (int) $f['id']) ?>">Editar</a>
                    <form method="post" action="<?= View::url('/admin/libros/eliminar') ?>" data-confirm="¿Eliminar «<?= View::e($f['titulo']) ?>»? Esta acción no se puede deshacer.">
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
<?= View::paginacion($pag, '/admin/libros') ?>
