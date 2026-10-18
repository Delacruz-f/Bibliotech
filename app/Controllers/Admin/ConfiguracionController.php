<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Flash;
use App\Models\Configuracion;

final class ConfiguracionController extends BaseController
{
    public function index(): void
    {
        $this->render('admin/configuracion/index', 'configuracion', 'Configuración del sistema', [
            'configuracion' => (new Configuracion())->obtenerTodas(),
            'error' => null,
        ]);
    }

    public function guardar(): void
    {
        $this->validarCsrf('/admin/configuracion');
        $bloqueoMultas = $_POST['bloquear_multas_pendientes'] ?? null;
        $datos = [
            'dias_prestamo' => $this->entero('dias_prestamo', 1, 60),
            'max_prestamos_activos' => $this->entero('max_prestamos_activos', 1, 99),
            'max_reservas_activas' => $this->entero('max_reservas_activas', 1, 99),
            'horas_vigencia_reserva' => $this->entero('horas_vigencia_reserva', 1, 720),
            'mora_por_dia' => $this->monto('mora_por_dia'),
            'dias_gracia' => $this->entero('dias_gracia', 0, 60),
            'bloquear_multas_pendientes' => $bloqueoMultas === null
                ? '0'
                : ($bloqueoMultas === '1' ? '1' : null),
        ];

        if (in_array(null, $datos, true)) {
            http_response_code(422);
            $this->render('admin/configuracion/index', 'configuracion', 'Configuración del sistema', [
                'configuracion' => $this->valoresPublicados(),
                'error' => 'Revisa los valores: deben ser números válidos dentro de los límites indicados.',
            ]);

            return;
        }

        /** @var array<string, string> $datos */
        (new Configuracion())->actualizarTodas($datos);
        Flash::success('La configuración del sistema fue actualizada.');
        $this->redirigir('/admin/configuracion');
    }

    private function entero(string $clave, int $minimo, int $maximo): ?string
    {
        $valor = $_POST[$clave] ?? null;
        if (!is_string($valor) || !preg_match('/^\d+$/', $valor)) {
            return null;
        }

        $numero = filter_var($valor, FILTER_VALIDATE_INT);
        if ($numero === false || $numero < $minimo || $numero > $maximo) {
            return null;
        }

        return (string) $numero;
    }

    private function monto(string $clave): ?string
    {
        $valor = $_POST[$clave] ?? null;
        if (!is_string($valor) || !preg_match('/^\d+(?:\.\d{1,2})?$/', $valor)) {
            return null;
        }

        $monto = (float) $valor;
        if ($monto <= 0 || $monto > 1000) {
            return null;
        }

        return number_format($monto, 2, '.', '');
    }

    /** @return array<string, string> */
    private function valoresPublicados(): array
    {
        $valores = [];
        foreach (Configuracion::VALORES_POR_DEFECTO as $clave => $defecto) {
            $valor = $_POST[$clave] ?? null;
            $valores[$clave] = is_string($valor) ? $valor : $defecto;
        }
        $valores['bloquear_multas_pendientes'] = isset($_POST['bloquear_multas_pendientes'])
            && $_POST['bloquear_multas_pendientes'] === '1' ? '1' : '0';

        return $valores;
    }
}
