<?php
declare(strict_types=1);

use App\Core\View;

/** @var list<array<string, mixed>> $filas */
/** @var App\Core\Paginator $pag */
/** @var string $q */
/** @var string $csrf */
?>
<section class="librarian-page-heading">
    <div>
        <p class="librarian-eyebrow">Circulación</p>
        <h1>Reservas pendientes</h1>
        <p><?= $pag->total ?> reserva(s) esperando confirmación de entrega.</p>
    </div>
</section>

<form class="librarian-filters" method="get" action="<?= View::url('/bibliotecario/reservas') ?>">
    <label class="sr-only" for="reservation-search">Buscar reservas</label>
    <input id="reservation-search" type="search" name="q" value="<?= View::e($q) ?>" placeholder="Buscar por libro o estudiante" maxlength="100">
    <button class="librarian-button button-secondary" type="submit">Buscar</button>
</form>

<?php if ($filas === []): ?>
    <section class="librarian-empty"><h2>No hay reservas pendientes</h2><p>Las nuevas reservas aparecerán aquí.</p></section>
<?php else: ?>
    <section class="reservation-grid" aria-label="Reservas pendientes">
        <?php foreach ($filas as $fila): ?>
            <article class="reservation-card">
                <div class="reservation-card-heading">
                    <span class="librarian-badge status-pendiente">Pendiente</span>
                    <span class="reservation-expiration">Expira <?= View::fecha($fila['fecha_expiracion'], true) ?></span>
                </div>
                <h2><?= View::e($fila['libro']) ?></h2>
                <p class="reservation-user"><?= View::e($fila['usuario']) ?></p>
                <p class="reservation-email"><?= View::e($fila['email']) ?></p>
                <dl>
                    <div><dt>Reservado</dt><dd><?= View::fecha($fila['fecha_reserva'], true) ?></dd></div>
                    <div><dt>Stock disponible</dt><dd><?= (int) $fila['stock_disponible'] ?></dd></div>
                </dl>
                <form method="post" action="<?= View::url('/bibliotecario/reservas/confirmar') ?>">
                    <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
                    <button class="librarian-button" type="submit">Confirmar préstamo</button>
                </form>
            </article>
        <?php endforeach; ?>
    </section>
    <?= View::paginacion($pag, '/bibliotecario/reservas') ?>
<?php endif; ?>
