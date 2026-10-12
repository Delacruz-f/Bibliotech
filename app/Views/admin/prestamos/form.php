<?php
declare(strict_types=1);

use App\Core\View;

/** @var array<string, string> $v */
/** @var string|null $error */
/** @var list<array<string, mixed>> $estudiantes */
/** @var list<array<string, mixed>> $libros */
/** @var string $csrf */
?>
<div class="adm-head">
    <div>
        <h1 class="adm-title">Registrar préstamo</h1>
        <p class="adm-subtitle">El stock del libro se descuenta automáticamente</p>
    </div>
    <a class="adm-btn adm-btn-ghost" href="<?= View::url('/admin/prestamos') ?>">Volver</a>
</div>

<?php if (!empty($error)): ?>
    <p class="adm-alert adm-alert-error" role="alert"><?= View::e($error) ?></p>
<?php endif; ?>

<form class="adm-form adm-form-narrow" method="post" action="<?= View::url('/admin/prestamos/guardar') ?>">
    <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">

    <label class="span2">Estudiante
        <select name="id_usuario" required>
            <option value="">Selecciona un estudiante…</option>
            <?php foreach ($estudiantes as $u): ?>
                <option value="<?= (int) $u['id'] ?>"<?= (string) $u['id'] === $v['id_usuario'] ? ' selected' : '' ?>><?= View::e($u['nombre']) ?> · <?= View::e($u['email']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="span2">Libro (solo con ejemplares disponibles)
        <select name="id_libro" required>
            <option value="">Selecciona un libro…</option>
            <?php foreach ($libros as $l): ?>
                <option value="<?= (int) $l['id'] ?>"<?= (string) $l['id'] === $v['id_libro'] ? ' selected' : '' ?>><?= View::e($l['titulo']) ?> — <?= View::e($l['autor']) ?> (<?= (int) $l['stock_disponible'] ?> disp.)</option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Días de préstamo
        <input name="dias" type="number" min="1" max="60" value="<?= View::e($v['dias']) ?>" required>
    </label>
    <div class="span2 adm-form-actions">
        <button class="adm-btn" type="submit">Registrar préstamo</button>
        <a class="adm-btn adm-btn-ghost" href="<?= View::url('/admin/prestamos') ?>">Cancelar</a>
    </div>
</form>
