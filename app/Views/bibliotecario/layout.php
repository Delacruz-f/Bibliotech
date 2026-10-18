<?php
declare(strict_types=1);

use App\Core\View;

/** @var string $content */
/** @var string $titulo */
/** @var string $seccion */
/** @var array<string, mixed> $usuario */
/** @var string $csrf */
/** @var list<array{type: string, message: string}> $mensajes */
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#6b2c5f">
    <title><?= View::e($titulo) ?> | Bibliotecario BiblioTech</title>
    <link rel="stylesheet" href="<?= View::url('/assets/css/bibliotecario.css') ?>">
</head>
<body class="librarian">
<?php require __DIR__ . '/_header.php'; ?>
<main class="librarian-main">
    <?php foreach ($mensajes as $mensaje): ?>
        <p class="librarian-alert <?= $mensaje['type'] === 'success' ? 'alert-success' : 'alert-error' ?>"
           role="<?= $mensaje['type'] === 'success' ? 'status' : 'alert' ?>"><?= View::e($mensaje['message']) ?></p>
    <?php endforeach; ?>
    <?= $content ?>
</main>
</body>
</html>
