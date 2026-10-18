<?php
declare(strict_types=1);

use App\Core\View;

/** @var string $content */
/** @var string $titulo */
/** @var string $seccion */
/** @var array<string, mixed> $usuario */
/** @var string $csrf */
/** @var list<array{type: string, message: string}> $mensajes */

$menu = [
    'dashboard' => ['Dashboard', '/admin'],
    'libros' => ['Libros', '/admin/libros'],
    'categorias' => ['Categorías', '/admin/categorias'],
    'usuarios' => ['Usuarios', '/admin/usuarios'],
    'configuracion' => ['Configuración', '/admin/configuracion'],
    'reservas' => ['Reservas', '/admin/reservas'],
    'prestamos' => ['Préstamos', '/admin/prestamos'],
    'multas' => ['Multas', '/admin/multas'],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#6f2459">
    <title><?= View::e($titulo) ?> | Admin BiblioTech</title>
    <link rel="stylesheet" href="<?= View::url('/assets/css/admin.css') ?>">
    <script src="<?= View::url('/assets/js/admin.js') ?>" defer></script>
</head>
<body class="adm<?= $seccion === 'configuracion' ? ' adm-config' : '' ?>">
<header class="adm-header">
    <a class="adm-brand" href="<?= View::url('/admin') ?>">BiblioTech</a>
    <nav class="adm-topnav" aria-label="Navegación principal">
        <a href="<?= View::url('/home') ?>">Inicio</a>
        <a href="<?= View::url('/catalogo') ?>">Catálogo</a>
        <a href="<?= View::url('/admin') ?>" aria-current="page">Admin</a>
        <span class="adm-user"><?= View::e($usuario['nombre'] ?? '') ?></span>
        <form action="<?= View::url('/logout') ?>" method="post">
            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
            <button class="adm-logout" type="submit">Salir</button>
        </form>
    </nav>
</header>

<div class="adm-layout">
    <aside class="adm-sidebar" aria-label="Administración">
        <h2>Administración</h2>
        <nav>
            <?php foreach ($menu as $clave => [$etiqueta, $ruta]): ?>
                <a href="<?= View::url($ruta) ?>"<?= $clave === $seccion ? ' class="active" aria-current="page"' : '' ?>><?= View::e($etiqueta) ?></a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <main class="adm-main">
        <?php foreach ($mensajes as $mensaje): ?>
            <p class="adm-alert adm-alert-<?= $mensaje['type'] === 'success' ? 'success' : 'error' ?>" role="<?= $mensaje['type'] === 'success' ? 'status' : 'alert' ?>"><?= View::e($mensaje['message']) ?></p>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
</div>
</body>
</html>
