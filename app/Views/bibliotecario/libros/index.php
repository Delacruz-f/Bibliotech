<?php
declare(strict_types=1);

use App\Core\View;

/** @var list<array<string, mixed>> $filas */
/** @var App\Core\Paginator $pag */
/** @var string $q */
?>
<section class="librarian-page-heading">
    <div>
        <p class="librarian-eyebrow">Consulta</p>
        <h1>Catálogo de libros</h1>
        <p><?= $pag->total ?> libro(s) · vista de solo lectura.</p>
    </div>
</section>

<form class="librarian-filters" method="get" action="<?= View::url('/bibliotecario/libros') ?>">
    <label class="sr-only" for="book-search">Buscar libros</label>
    <input id="book-search" type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar por título, autor o ISBN" maxlength="100">
    <button class="librarian-button button-secondary" type="submit">Buscar</button>
</form>

<div class="librarian-table-wrap">
    <table class="librarian-table">
        <thead><tr><th>Libro</th><th>Autor</th><th>ISBN</th><th>Categoría</th><th>Stock</th></tr></thead>
        <tbody>
        <?php if ($filas === []): ?>
            <tr><td colspan="5" class="table-empty">No hay libros que coincidan con la búsqueda.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $fila): ?>
            <?php $disponibles = (int) $fila['stock_disponible']; ?>
            <tr>
                <td><strong><?= View::e($fila['titulo']) ?></strong></td>
                <td><?= View::e($fila['autor']) ?></td>
                <td><?= View::e($fila['isbn']) ?></td>
                <td><?= View::e($fila['categoria'] ?? 'Sin categoría') ?></td>
                <td><span class="stock-badge <?= $disponibles > 0 ? 'stock-available' : 'stock-empty' ?>"><?= $disponibles ?> / <?= (int) $fila['stock_total'] ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= View::paginacion($pag, '/bibliotecario/libros') ?>
