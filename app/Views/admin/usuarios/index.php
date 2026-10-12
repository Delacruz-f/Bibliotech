<?php
declare(strict_types=1);

use App\Controllers\Admin\UsuarioController;
use App\Core\Url;
use App\Core\View;

/** @var list<array<string, mixed>> $filas */
/** @var App\Core\Paginator $pag */
/** @var string $q */
/** @var string $csrf */
/** @var array<string, mixed> $usuario */
?>
<div class="adm-head">
    <div>
        <h1 class="adm-title">Usuarios</h1>
        <p class="adm-subtitle"><?= $pag->total ?> usuario(s) registrado(s)</p>
    </div>
    <a class="adm-btn" href="<?= View::url('/admin/usuarios/crear') ?>">+ Nuevo usuario</a>
</div>

<form class="adm-search" method="get" action="<?= View::url('/admin/usuarios') ?>">
    <input type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar por nombre o correo" maxlength="100" aria-label="Buscar usuarios">
    <button class="adm-btn adm-btn-ghost" type="submit">Buscar</button>
</form>

<div class="adm-table-wrap">
    <table class="adm-table">
        <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th class="right">Acciones</th></tr></thead>
        <tbody>
        <?php if ($filas === []): ?>
            <tr><td colspan="5" class="adm-empty">No se encontraron usuarios.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $f): ?>
            <tr>
                <td><strong><?= View::e($f['nombre']) ?></strong><?= (int) $f['id'] === (int) $usuario['id'] ? ' <small>(tú)</small>' : '' ?></td>
                <td><?= View::e($f['email']) ?></td>
                <td><?= View::badge((string) $f['rol'], UsuarioController::ROLES[$f['rol']] ?? null) ?></td>
                <td><?= View::badge((string) $f['estado'], UsuarioController::ESTADOS[$f['estado']] ?? null) ?></td>
                <td class="right actions">
                    <a class="adm-link" href="<?= View::e(Url::to('/admin/usuarios/editar') . '?id=' . (int) $f['id']) ?>">Editar</a>
                    <?php if ((int) $f['id'] !== (int) $usuario['id']): ?>
                        <form method="post" action="<?= View::url('/admin/usuarios/eliminar') ?>" data-confirm="¿Eliminar al usuario «<?= View::e($f['nombre']) ?>»?">
                            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                            <button class="adm-link adm-danger" type="submit">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= View::paginacion($pag, '/admin/usuarios') ?>
