<?php
declare(strict_types=1);

use App\Core\View;

/** @var array<string, mixed>|null $editado */
/** @var array<string, mixed> $v */
/** @var string|null $error */
/** @var array<string, string> $roles */
/** @var array<string, string> $estados */
/** @var bool $esYo */
/** @var string $csrf */
?>
<div class="adm-head">
    <div>
        <h1 class="adm-title"><?= $editado ? 'Editar usuario' : 'Nuevo usuario' ?></h1>
        <p class="adm-subtitle"><?= $editado ? 'Deja la contraseña vacía para conservar la actual' : 'Crea una cuenta con el rol que necesites' ?></p>
    </div>
    <a class="adm-btn adm-btn-ghost" href="<?= View::url('/admin/usuarios') ?>">Volver</a>
</div>

<?php if (!empty($error)): ?>
    <p class="adm-alert adm-alert-error" role="alert"><?= View::e($error) ?></p>
<?php endif; ?>

<form class="adm-form" method="post" action="<?= View::url('/admin/usuarios/guardar') ?>" autocomplete="off">
    <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
    <input type="hidden" name="id" value="<?= $editado ? (int) $editado['id'] : 0 ?>">

    <label>Nombre completo
        <input name="nombre" value="<?= View::e($v['nombre']) ?>" maxlength="120" required>
    </label>
    <label>Correo electrónico
        <input name="email" type="email" value="<?= View::e($v['email']) ?>" maxlength="180" required>
    </label>
    <label>Rol
        <select name="rol"<?= $esYo ? ' aria-describedby="yo"' : '' ?>>
            <?php foreach ($roles as $valor => $etiqueta): ?>
                <option value="<?= View::e($valor) ?>"<?= $valor === $v['rol'] ? ' selected' : '' ?>><?= View::e($etiqueta) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Estado
        <select name="estado">
            <?php foreach ($estados as $valor => $etiqueta): ?>
                <option value="<?= View::e($valor) ?>"<?= $valor === $v['estado'] ? ' selected' : '' ?>><?= View::e($etiqueta) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php if ($esYo): ?>
        <p class="span2 adm-hint" id="yo">Estás editando tu propia cuenta: no puedes cambiar tu rol ni bloquearte.</p>
    <?php endif; ?>
    <label>Contraseña <?= $editado ? '(opcional)' : '' ?>
        <input name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password"<?= $editado ? '' : ' required' ?>>
    </label>
    <label>Confirmar contraseña
        <input name="password_confirmation" type="password" minlength="8" maxlength="72" autocomplete="new-password"<?= $editado ? '' : ' required' ?>>
    </label>

    <div class="span2 adm-form-actions">
        <button class="adm-btn" type="submit">Guardar usuario</button>
        <a class="adm-btn adm-btn-ghost" href="<?= View::url('/admin/usuarios') ?>">Cancelar</a>
    </div>
</form>
