<?php
declare(strict_types=1);

use App\Core\View;

/** @var array<string, int|float> $t */
/** @var list<array{clave: string, total: int, etiqueta: string}> $serie */
/** @var list<array<string, mixed>> $topLibros */
/** @var list<array<string, mixed>> $topEstudiantes */
/** @var list<array<string, mixed>> $sinStock */
/** @var list<array<string, mixed>> $porExpirar */

// Gráfico SVG (atributos, no estilos en línea: la CSP bloquea style="").
$maximo = max(1, ...array_column($serie, 'total'));
$altoGrafico = 160;
$anchoColumna = 100;
?>
<h1 class="adm-title">Reporte general</h1>
<p class="adm-subtitle">Estado actual de BiblioTech · <?= date('d/m/Y') ?></p>

<section class="adm-cards" aria-label="Resumen">
    <article class="adm-card adm-card-lg">
        <span class="adm-card-label">Catálogo</span>
        <strong class="adm-card-value"><?= (int) $t['libros'] ?></strong>
        <span class="adm-card-note"><?= (int) $t['ejemplares'] ?> ejemplares · <?= (int) $t['disponibles'] ?> disponibles</span>
    </article>
    <article class="adm-card adm-card-lg">
        <span class="adm-card-label">Préstamos activos</span>
        <strong class="adm-card-value"><?= (int) $t['prestamos_activos'] ?></strong>
        <span class="adm-card-note"><?= (int) $t['prestamos_vencidos'] ?> vencidos</span>
    </article>
    <article class="adm-card adm-card-lg">
        <span class="adm-card-label">Reservas activas</span>
        <strong class="adm-card-value"><?= (int) $t['reservas_activas'] ?></strong>
        <span class="adm-card-note">Pendientes de retiro</span>
    </article>
    <article class="adm-card adm-card-lg">
        <span class="adm-card-label">Multas pendientes</span>
        <strong class="adm-card-value adm-gold"><?= View::soles($t['monto_pendiente']) ?></strong>
        <span class="adm-card-note"><?= (int) $t['multas_pendientes'] ?> registros</span>
    </article>
    <article class="adm-card">
        <span class="adm-card-label">Estudiantes</span>
        <strong class="adm-card-value"><?= (int) $t['estudiantes'] ?></strong>
    </article>
    <article class="adm-card">
        <span class="adm-card-label">Bibliotecarios</span>
        <strong class="adm-card-value"><?= (int) $t['bibliotecarios'] ?></strong>
    </article>
    <article class="adm-card">
        <span class="adm-card-label">Préstamos este mes</span>
        <strong class="adm-card-value"><?= (int) $t['prestamos_mes'] ?></strong>
    </article>
    <article class="adm-card">
        <span class="adm-card-label">Cobrado este mes</span>
        <strong class="adm-card-value adm-green"><?= View::soles($t['cobrado_mes']) ?></strong>
    </article>
</section>

<section class="adm-panel" aria-labelledby="h-meses">
    <h2 id="h-meses">Préstamos últimos 6 meses</h2>
    <svg class="adm-chart" viewBox="0 0 <?= $anchoColumna * count($serie) ?> <?= $altoGrafico + 50 ?>" role="img"
         aria-label="Préstamos por mes: <?= View::e(implode(', ', array_map(static fn ($m) => $m['etiqueta'] . ' ' . $m['total'], $serie))) ?>">
        <line x1="0" y1="<?= $altoGrafico + 10 ?>" x2="<?= $anchoColumna * count($serie) ?>" y2="<?= $altoGrafico + 10 ?>" class="chart-axis"/>
        <?php foreach ($serie as $i => $mes):
            $alto = (int) round(($mes['total'] / $maximo) * $altoGrafico);
            $x = $i * $anchoColumna + 25;
            $y = $altoGrafico + 10 - $alto; ?>
            <?php if ($alto > 0): ?>
                <rect x="<?= $x ?>" y="<?= $y ?>" width="50" height="<?= $alto ?>" rx="6" class="chart-bar"/>
            <?php endif; ?>
            <text x="<?= $x + 25 ?>" y="<?= max(14, $y - 6) ?>" text-anchor="middle" class="chart-value"><?= (int) $mes['total'] ?></text>
            <text x="<?= $x + 25 ?>" y="<?= $altoGrafico + 34 ?>" text-anchor="middle" class="chart-label"><?= View::e($mes['etiqueta']) ?></text>
        <?php endforeach; ?>
    </svg>
</section>

<div class="adm-two">
    <section class="adm-panel" aria-labelledby="h-libros">
        <h2 id="h-libros">Top 5 libros más prestados</h2>
        <?php if ($topLibros === []): ?>
            <p class="adm-empty">Aún no hay préstamos registrados.</p>
        <?php else: ?>
            <ol class="adm-rank">
                <?php foreach ($topLibros as $fila): ?>
                    <li><span><?= View::e($fila['titulo']) ?><small><?= View::e($fila['autor']) ?></small></span><b><?= (int) $fila['total'] ?></b></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>

    <section class="adm-panel" aria-labelledby="h-est">
        <h2 id="h-est">Top 5 estudiantes con más préstamos</h2>
        <?php if ($topEstudiantes === []): ?>
            <p class="adm-empty">Aún no hay préstamos registrados.</p>
        <?php else: ?>
            <ol class="adm-rank">
                <?php foreach ($topEstudiantes as $fila): ?>
                    <li><span><?= View::e($fila['nombre']) ?><small><?= View::e($fila['email']) ?></small></span><b><?= (int) $fila['total'] ?></b></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>
</div>

<div class="adm-two">
    <section class="adm-panel" aria-labelledby="h-stock">
        <h2 id="h-stock">Libros sin stock</h2>
        <?php if ($sinStock === []): ?>
            <p class="adm-empty">Todos los libros tienen ejemplares disponibles.</p>
        <?php else: ?>
            <ul class="adm-alerts">
                <?php foreach ($sinStock as $fila): ?>
                    <li><a href="<?= View::e(\App\Core\Url::to('/admin/libros/editar') . '?id=' . (int) $fila['id']) ?>"><?= View::e($fila['titulo']) ?></a>
                        <small><?= View::e($fila['autor']) ?> · 0/<?= (int) $fila['stock_total'] ?></small></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="adm-panel" aria-labelledby="h-exp">
        <h2 id="h-exp">Reservas que expiran en 24 h</h2>
        <?php if ($porExpirar === []): ?>
            <p class="adm-empty">No hay reservas por expirar.</p>
        <?php else: ?>
            <ul class="adm-alerts">
                <?php foreach ($porExpirar as $fila): ?>
                    <li><a href="<?= View::url('/admin/reservas') ?>"><?= View::e($fila['libro']) ?></a>
                        <small><?= View::e($fila['usuario']) ?> · vence <?= View::fecha($fila['fecha_expiracion'], true) ?></small></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
