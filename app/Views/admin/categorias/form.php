<?php
declare(strict_types=1);

use App\Core\View;

/** @var array<string, mixed>|null $categoria */
/** @var array<string, mixed> $v */
/** @var string|null $error */
/** @var string $csrf */
?>
<div class="adm-head">
    <div>
        <h1 class="adm-title"><?= $categoria ? 'Editar categoría' : 'Nueva categoría' ?></h1>
        <p class="adm-subtitle">Agrupa los libros del catálogo</p>
    </div>
    <a class="adm-btn adm-btn-ghost" href="<?= View::url('/admin/categorias') ?>">Volver</a>
</div>

<?php if (!empty($error)): ?>
    <p class="adm-alert adm-alert-error" role="alert"><?= View::e($error) ?></p>
<?php endif; ?>

<form class="adm-form adm-form-narrow" method="post" action="<?= View::url('/admin/categorias/guardar') ?>">
    <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
    <input type="hidden" name="id" value="<?= $categoria ? (int) $categoria['id'] : 0 ?>">

    <label class="span2">Nombre
        <input name="nombre" value="<?= View::e($v['nombre']) ?>" maxlength="100" required>
    </label>
    <label class="span2">Descripción
        <textarea name="descripcion" rows="4" maxlength="2000"><?= View::e($v['descripcion'] ?? '') ?></textarea>
    </label>
    <div class="span2 adm-form-actions">
        <button class="adm-btn" type="submit">Guardar categoría</button>
        <a class="adm-btn adm-btn-ghost" href="<?= View::url('/admin/categorias') ?>">Cancelar</a>
    </div>
</form>
