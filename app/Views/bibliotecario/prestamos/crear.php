<?php
declare(strict_types=1);

use App\Core\View;

/** @var array<string, string> $valores */
/** @var string|null $error */
/** @var list<array<string, mixed>> $estudiantes */
/** @var list<array<string, mixed>> $libros */
/** @var string $csrf */
?>
<section class="librarian-page-heading">
    <div>
        <p class="librarian-eyebrow">Circulación</p>
        <h1>Registrar préstamo</h1>
        <p>El plazo configurado se aplica automáticamente. El stock se descuenta al confirmar.</p>
    </div>
    <a class="librarian-button button-secondary" href="<?= View::url('/bibliotecario/prestamos') ?>">Volver</a>
</section>

<?php if ($error !== null): ?>
    <p class="librarian-alert alert-error" role="alert"><?= View::e($error) ?></p>
<?php endif; ?>

<form class="librarian-form" method="post" action="<?= View::url('/bibliotecario/prestamos/guardar') ?>">
    <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
    <label for="student-id">Estudiante activo
        <select id="student-id" name="id_usuario" required>
            <option value="">Selecciona un estudiante...</option>
            <?php foreach ($estudiantes as $estudiante): ?>
                <option value="<?= (int) $estudiante['id'] ?>"<?= (string) $estudiante['id'] === $valores['id_usuario'] ? ' selected' : '' ?>>
                    <?= View::e($estudiante['nombre']) ?> · <?= View::e($estudiante['email']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label for="book-id">Libro con stock disponible
        <select id="book-id" name="id_libro" required>
            <option value="">Selecciona un libro...</option>
            <?php foreach ($libros as $libro): ?>
                <option value="<?= (int) $libro['id'] ?>"<?= (string) $libro['id'] === $valores['id_libro'] ? ' selected' : '' ?>>
                    <?= View::e($libro['titulo']) ?> — <?= View::e($libro['autor']) ?> (<?= (int) $libro['stock_disponible'] ?> disponibles)
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php if ($estudiantes === []): ?><p class="form-note">No hay estudiantes activos disponibles para seleccionar.</p><?php endif; ?>
    <?php if ($libros === []): ?><p class="form-note">No hay ejemplares disponibles para prestar.</p><?php endif; ?>
    <div class="form-actions">
        <button class="librarian-button" type="submit">Registrar préstamo</button>
        <a class="librarian-button button-secondary" href="<?= View::url('/bibliotecario/prestamos') ?>">Cancelar</a>
    </div>
</form>
