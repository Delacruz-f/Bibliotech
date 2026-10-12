<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Reporte;
use App\Services\Circulacion;

final class DashboardController extends BaseController
{
    private const MESES = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public function index(): void
    {
        (new Circulacion())->actualizarEstados();
        $reporte = new Reporte();

        $serie = array_map(static function (array $mes): array {
            [$anio, $m] = explode('-', $mes['clave']);
            $mes['etiqueta'] = self::MESES[(int) $m] . ' ' . substr($anio, 2);

            return $mes;
        }, $reporte->prestamosUltimosMeses(6));

        $this->render('admin/dashboard', 'dashboard', 'Reporte general', [
            't' => $reporte->tarjetas(),
            'serie' => $serie,
            'topLibros' => $reporte->topLibros(5),
            'topEstudiantes' => $reporte->topEstudiantes(5),
            'sinStock' => $reporte->librosSinStock(),
            'porExpirar' => $reporte->reservasPorExpirar(),
        ]);
    }
}
