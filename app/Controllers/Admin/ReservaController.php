<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Flash;
use App\Core\Paginator;
use App\Models\Reserva;
use App\Services\Circulacion;
use DomainException;

final class ReservaController extends BaseController
{
    public function index(): void
    {
        (new Circulacion())->actualizarEstados();

        $reservas = new Reserva();
        $q = $this->busqueda();
        // Por defecto se muestran las reservas activas (pendientes de retiro). "todas" las lista todas.
        $estado = isset($_GET['estado']) ? $this->texto('estado', $_GET) : 'pendiente';
        $pag = new Paginator($reservas->contar($estado, $q), self::POR_PAGINA, $this->pagina());

        $this->render('admin/reservas/index', 'reservas', 'Reservas', [
            'filas' => $reservas->listar($estado, $q, $pag->perPage, $pag->offset),
            'pag' => $pag,
            'q' => $q,
            'estado' => $estado,
            'estados' => Reserva::ESTADOS,
        ]);
    }

    public function confirmar(): void
    {
        $this->validarCsrf('/admin/reservas');
        try {
            (new Circulacion())->confirmarReserva($this->idDe($_POST));
            Flash::success('Entrega confirmada: se generó el préstamo y se descontó el stock.');
        } catch (DomainException $e) {
            Flash::error($e->getMessage());
        }
        $this->redirigir('/admin/reservas');
    }

    public function cancelar(): void
    {
        $this->validarCsrf('/admin/reservas');
        try {
            (new Circulacion())->cancelarReserva($this->idDe($_POST));
            Flash::success('Reserva cancelada.');
        } catch (DomainException $e) {
            Flash::error($e->getMessage());
        }
        $this->redirigir('/admin/reservas');
    }
}
