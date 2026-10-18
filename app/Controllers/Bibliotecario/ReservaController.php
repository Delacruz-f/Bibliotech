<?php
declare(strict_types=1);

namespace App\Controllers\Bibliotecario;

use App\Core\Flash;
use App\Core\Paginator;
use App\Models\Reserva;
use App\Services\Circulacion;
use DomainException;

final class ReservaController extends BaseController
{
    public function index(): void
    {
        $circulacion = new Circulacion();
        $circulacion->actualizarEstados();

        $reservas = new Reserva();
        $q = $this->busqueda();
        $pag = new Paginator($reservas->contar('pendiente', $q), self::POR_PAGINA, $this->pagina());
        $this->render('bibliotecario/reservas/index', 'reservas', 'Reservas pendientes', [
            'filas' => $reservas->listar('pendiente', $q, $pag->perPage, $pag->offset),
            'pag' => $pag,
            'q' => $q,
        ]);
    }

    public function confirmar(): void
    {
        $this->validarCsrf('/bibliotecario/reservas');
        try {
            (new Circulacion())->confirmarReserva($this->idPost());
            Flash::success('Reserva confirmada: se generó el préstamo y se descontó el stock.');
        } catch (DomainException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirigir('/bibliotecario/reservas');
    }
}
