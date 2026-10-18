<?php
declare(strict_types=1);

use App\Core\View;

$menu = [
    'dashboard' => ['Inicio', '/bibliotecario'],
    'libros' => ['Libros', '/bibliotecario/libros'],
    'prestamos' => ['Préstamos', '/bibliotecario/prestamos'],
    'reservas' => ['Reservas', '/bibliotecario/reservas'],
    'multas' => ['Multas', '/bibliotecario/multas'],
];
?>
<header class="librarian-header">
    <a class="librarian-brand" href="<?= View::url('/bibliotecario') ?>">BiblioTech</a>
    <nav class="librarian-nav" aria-label="Navegación de bibliotecario">
        <?php foreach ($menu as $clave => [$etiqueta, $ruta]): ?>
            <a href="<?= View::url($ruta) ?>"<?= $seccion === $clave ? ' class="active" aria-current="page"' : '' ?>><?= View::e($etiqueta) ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="librarian-account">
        <span><?= View::e($usuario['nombre'] ?? '') ?></span>
        <form action="<?= View::url('/logout') ?>" method="post">
            <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
            <button type="submit">Salir</button>
        </form>
    </div>
</header>
