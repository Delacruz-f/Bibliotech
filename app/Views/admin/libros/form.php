<?php
declare(strict_types=1);

use App\Core\View;

/** @var array<string, mixed>|null $libro */
/** @var array<string, mixed> $v */
/** @var string|null $error */
/** @var list<array<string, mixed>> $categorias */
/** @var string $csrf */
$prestados = $libro ? (int) $libro['stock_total'] - (int) $libro['stock_disponible'] : 0;
?>
<div class="adm-head">
    <div>
        <h1 class="adm-title"><?= $libro ? 'Editar libro' : 'Nuevo libro' ?></h1>
        <p class="adm-subtitle"><?= $libro ? 'Stock disponible: ' . (int) $libro['stock_disponible'] . '/' . (int) $libro['stock_total'] : 'Completa los datos del libro' ?></p>
    </div>
    <a class="adm-btn adm-btn-ghost" href="<?= View::url('/admin/libros') ?>">Volver</a>
</div>

<?php if (!empty($error)): ?>
    <p class="adm-alert adm-alert-error" role="alert"><?= View::e($error) ?></p>
<?php endif; ?>

<form class="adm-form" method="post" action="<?= View::url('/admin/libros/guardar') ?>">
    <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
    <input type="hidden" name="id" value="<?= $libro ? (int) $libro['id'] : 0 ?>">

    <label class="span2">Título
        <input name="titulo" value="<?= View::e($v['titulo']) ?>" maxlength="255" required>
    </label>
    <label>Autor
        <input name="autor" value="<?= View::e($v['autor']) ?>" maxlength="180" required>
    </label>
    <label>ISBN
        <input name="isbn" value="<?= View::e($v['isbn']) ?>" maxlength="20" required>
    </label>
    <label>Categoría
        <select name="id_categoria" required>
            <option value="">Selecciona…</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['id'] ?>"<?= (string) $c['id'] === (string) $v['id_categoria'] ? ' selected' : '' ?>><?= View::e($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Editorial
        <input name="editorial" value="<?= View::e($v['editorial'] ?? '') ?>" maxlength="150">
    </label>
    <label>Año
        <input name="anio" type="number" min="1000" max="<?= (int) date('Y') + 1 ?>" value="<?= View::e($v['anio'] ?? '') ?>">
    </label>
    <label>Stock total (ejemplares)
        <input name="stock_total" type="number" min="<?= $prestados ?>" max="9999" value="<?= View::e($v['stock_total']) ?>" required>
        <?php if ($prestados > 0): ?><small>Hay <?= $prestados ?> ejemplar(es) prestado(s); el total no puede ser menor.</small><?php endif; ?>
    </label>
    <label class="span2">URL de la portada (opcional)
        <input name="portada_url" type="url" value="<?= View::e($v['portada_url'] ?? '') ?>" maxlength="500" placeholder="https://…">
    </label>
    <label class="span2">Sinopsis
        <textarea name="sinopsis" rows="5" maxlength="5000"><?= View::e($v['sinopsis'] ?? '') ?></textarea>
    </label>

    <div class="span2 adm-form-actions">
        <button class="adm-btn" type="submit">Guardar libro</button>
        <a class="adm-btn adm-btn-ghost" href="<?= View::url('/admin/libros') ?>">Cancelar</a>
    </div>
</form>
