<?php
declare(strict_types=1);

namespace App\Controllers\Bibliotecario;

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

        $this->render('bibliotecario/prestamos/index', 'prestamos', 'Préstamos', [
            'filas' => $prestamos->listar($estado, $q, $pag->perPage, $pag->offset),
            'pag' => $pag,
            'q' => $q,
            'estado' => $estado,
            'estados' => Prestamo::ESTADOS,
        ]);
    }

    public function crear(): void
    {
        $this->formulario(['id_usuario' => '', 'id_libro' => '']);
    }

    public function guardar(): void
    {
        $this->validarCsrf('/bibliotecario/prestamos/crear');
        $valores = [
            'id_usuario' => $this->texto('id_usuario'),
            'id_libro' => $this->texto('id_libro'),
        ];
        $idUsuario = filter_var($valores['id_usuario'], FILTER_VALIDATE_INT);
        $idLibro = filter_var($valores['id_libro'], FILTER_VALIDATE_INT);

        try {
            if ($idUsuario === false || $idUsuario === null || $idUsuario < 1
                || $idLibro === false || $idLibro === null || $idLibro < 1) {
                throw new DomainException('Selecciona un estudiante y un libro disponible.');
            }

            (new Circulacion())->registrarPrestamo($idUsuario, $idLibro);
        } catch (DomainException $exception) {
            http_response_code(422);
            $this->formulario($valores, $exception->getMessage());

            return;
        }

        Flash::success('Préstamo registrado según el plazo configurado. El stock del libro se actualizó.');
        $this->redirigir('/bibliotecario/prestamos');
    }

    public function devolver(): void
    {
        $this->validarCsrf('/bibliotecario/prestamos');
        try {
            $monto = (new Circulacion())->registrarDevolucion($this->idPost());
            Flash::success($monto > 0
                ? 'Devolución registrada. Se generó una multa de ' . View::soles($monto) . '.'
                : 'Devolución registrada y stock actualizado.');
        } catch (DomainException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirigir('/bibliotecario/prestamos');
    }

    /** @param array<string, string> $valores */
    private function formulario(array $valores, ?string $error = null): void
    {
        $this->render('bibliotecario/prestamos/crear', 'prestamos', 'Registrar préstamo', [
            'valores' => $valores,
            'error' => $error,
            'estudiantes' => (new UsuarioAdmin())->activosPorRol('estudiante'),
            'libros' => (new Libro())->disponibles(),
        ]);
    }
}
