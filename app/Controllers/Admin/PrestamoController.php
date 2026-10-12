<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Flash;
use App\Core\Paginator;
use App\Core\View;
use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\UsuarioAdmin;
use App\Services\Circulacion;
use DomainException;

final class PrestamoController extends BaseController
{
    public function index(): void
    {
        $circulacion = new Circulacion();
        $circulacion->actualizarEstados();

        $prestamos = new Prestamo();
        $q = $this->busqueda();
        $estado = $this->texto('estado', $_GET);
        $pag = new Paginator($prestamos->contar($estado, $q), self::POR_PAGINA, $this->pagina());

        $this->render('admin/prestamos/index', 'prestamos', 'Préstamos', [
            'filas' => $prestamos->listar($estado, $q, $pag->perPage, $pag->offset),
            'pag' => $pag,
            'q' => $q,
            'estado' => $estado,
            'estados' => Prestamo::ESTADOS,
        ]);
    }

    public function crear(): void
    {
        $this->formulario(['id_usuario' => '', 'id_libro' => '', 'dias' => (string) Circulacion::DIAS_PRESTAMO]);
    }

    public function guardar(): void
    {
        $this->validarCsrf('/admin/prestamos');
        $v = [
            'id_usuario' => $this->texto('id_usuario'),
            'id_libro' => $this->texto('id_libro'),
            'dias' => $this->texto('dias'),
        ];
        $dias = ctype_digit($v['dias']) ? (int) $v['dias'] : 0;

        try {
            if (!ctype_digit($v['id_usuario']) || !ctype_digit($v['id_libro'])) {
                throw new DomainException('Selecciona un usuario y un libro.');
            }
            if ($dias < 1 || $dias > 60) {
                throw new DomainException('Los días de préstamo deben estar entre 1 y 60.');
            }
            (new Circulacion())->registrarPrestamo((int) $v['id_usuario'], (int) $v['id_libro'], $dias);
        } catch (DomainException $e) {
            http_response_code(422);
            $this->formulario($v, $e->getMessage());

            return;
        }

        Flash::success('Préstamo registrado. El stock del libro se actualizó.');
        $this->redirigir('/admin/prestamos');
    }

    public function devolver(): void
    {
        $this->validarCsrf('/admin/prestamos');
        try {
            $multa = (new Circulacion())->registrarDevolucion($this->idDe($_POST));
            Flash::success($multa > 0
                ? 'Devolución registrada. Se generó una multa de ' . View::soles($multa) . ' por el retraso.'
                : 'Devolución registrada. El stock del libro se actualizó.');
        } catch (DomainException $e) {
            Flash::error($e->getMessage());
        }
        $this->redirigir('/admin/prestamos');
    }

    /** @param array<string, string> $v */
    private function formulario(array $v, ?string $error = null): void
    {
        $this->render('admin/prestamos/form', 'prestamos', 'Registrar préstamo', [
            'v' => $v,
            'error' => $error,
            'estudiantes' => (new UsuarioAdmin())->activosPorRol('estudiante'),
            'libros' => (new Libro())->disponibles(),
        ]);
    }
}
