<?php
declare(strict_types=1);

use App\Core\View;

/** @var array<string, string> $configuracion */
/** @var string|null $error */
/** @var string $csrf */
?>
<div class="adm-head">
    <div>
        <h1 class="adm-title">Configuración del sistema</h1>
        <p class="adm-subtitle">Ajusta las reglas de negocio sin tocar código.</p>
    </div>
</div>

<?php if ($error !== null): ?>
    <p class="adm-alert adm-alert-error" role="alert"><?= View::e($error) ?></p>
<?php endif; ?>

<form class="config-form" method="post" action="<?= View::url('/admin/configuracion/guardar') ?>">
    <input type="hidden" name="_csrf_token" value="<?= View::e($csrf) ?>">

    <section class="config-section" aria-labelledby="config-loans">
        <h2 id="config-loans">Préstamos</h2>
        <div class="config-fields">
            <label for="dias-prestamo">Días de préstamo
                <input id="dias-prestamo" type="number" name="dias_prestamo" min="1" max="60" step="1"
                       value="<?= View::e($configuracion['dias_prestamo']) ?>" required>
                <small>Plazo máximo para devolver un libro.</small>
            </label>
            <label for="max-prestamos">Máximo de préstamos activos
                <input id="max-prestamos" type="number" name="max_prestamos_activos" min="1" max="99" step="1"
                       value="<?= View::e($configuracion['max_prestamos_activos']) ?>" required>
            </label>
        </div>
    </section>

    <section class="config-section" aria-labelledby="config-reservations">
        <h2 id="config-reservations">Reservas</h2>
        <div class="config-fields">
            <label for="max-reservas">Máximo de reservas activas
                <input id="max-reservas" type="number" name="max_reservas_activas" min="1" max="99" step="1"
                       value="<?= View::e($configuracion['max_reservas_activas']) ?>" required>
            </label>
            <label for="horas-reserva">Horas de vigencia de la reserva
                <input id="horas-reserva" type="number" name="horas_vigencia_reserva" min="1" max="720" step="1"
                       value="<?= View::e($configuracion['horas_vigencia_reserva']) ?>" required>
            </label>
        </div>
    </section>

    <section class="config-section" aria-labelledby="config-fines">
        <h2 id="config-fines">Multas</h2>
        <div class="config-fields">
            <label for="mora-dia">Mora por día (S/)
                <input id="mora-dia" type="number" name="mora_por_dia" min="0.01" max="1000" step="0.01"
                       value="<?= View::e($configuracion['mora_por_dia']) ?>" required>
            </label>
            <label for="dias-gracia">Días de gracia
                <input id="dias-gracia" type="number" name="dias_gracia" min="0" max="60" step="1"
                       value="<?= View::e($configuracion['dias_gracia']) ?>" required>
                <small>Días antes de empezar a generar multa.</small>
            </label>
        </div>
        <label class="config-checkbox" for="bloquear-multas">
            <input id="bloquear-multas" type="checkbox" name="bloquear_multas_pendientes" value="1"
                   <?= $configuracion['bloquear_multas_pendientes'] === '1' ? 'checked' : '' ?>>
            <span>
                Bloquear usuarios con multas pendientes
                <small>Si está activo, no podrán reservar ni pedir nuevos préstamos.</small>
            </span>
        </label>
    </section>

    <div class="config-actions">
        <a href="<?= View::url('/admin') ?>">Cancelar</a>
        <button class="adm-btn config-save" type="submit">Guardar cambios</button>
    </div>
</form>
