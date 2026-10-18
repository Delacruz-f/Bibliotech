<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Url;
use App\Models\Configuracion;
use App\Models\Reserva;
use DomainException;

final class ReservaController
{
    public function index(): void
    {
        $idUsuario = $this->requireStudent();
        $reservas = (new Reserva())->obtenerPorUsuario($idUsuario);
        $flash = Flash::consume();
        $csrfToken = Csrf::token();
        $pageTitle = 'Mis reservas';

        require dirname(__DIR__) . '/Views/estudiante/mis_reservas.php';
    }

    public function crear(): void
    {
        $idUsuario = $this->requireStudent();
        if (!$this->validCsrf()) {
            $this->respondCsrfFailure('/catalogo');
        }

        $idLibro = filter_var($_POST['id_libro'] ?? null, FILTER_VALIDATE_INT);
        if (!is_int($idLibro) || $idLibro < 1) {
            Flash::set(['error' => 'No se recibió un libro válido.']);
            $this->redirect('/catalogo');
        }

        try {
            if ((new Reserva())->crear($idUsuario, $idLibro)) {
                $configuracion = (new Configuracion())->obtenerTodas();
                Flash::set(['success' => 'Reserva creada. Tienes ' . $configuracion['horas_vigencia_reserva'] . ' horas para retirarla.']);
            } else {
                Flash::set(['error' => 'El libro ya no está disponible para reservar.']);
            }
        } catch (DomainException $exception) {
            Flash::set(['error' => $exception->getMessage()]);
        }

        $this->redirect('/mis-reservas');
    }

    public function cancelar(): void
    {
        $idUsuario = $this->requireStudent();
        if (!$this->validCsrf()) {
            $this->respondCsrfFailure('/mis-reservas');
        }

        $idReserva = filter_var($_POST['id_reserva'] ?? null, FILTER_VALIDATE_INT);
        if (!is_int($idReserva) || $idReserva < 1) {
            Flash::set(['error' => 'No se recibió una reserva válida.']);
        } elseif ((new Reserva())->cancelar($idUsuario, $idReserva)) {
            Flash::set(['success' => 'La reserva fue cancelada.']);
        } else {
            Flash::set(['error' => 'No se pudo cancelar: la reserva no existe o ya no está pendiente.']);
        }

        $this->redirect('/mis-reservas');
    }

    private function requireStudent(): int
    {
        $user = $_SESSION['user'] ?? null;
        if (
            !is_array($user)
            || !isset($user['id'])
            || (int) $user['id'] < 1
        ) {
            $this->redirect('/login');
        }

        if (($user['rol'] ?? '') !== 'estudiante') {
            $this->redirect('/home');
        }

        return (int) $user['id'];
    }

    private function validCsrf(): bool
    {
        return Csrf::isValid($_POST['_csrf_token'] ?? null);
    }

    private function respondCsrfFailure(string $redirectPath): void
    {
        http_response_code(419);
        Flash::set(['error' => 'La solicitud expiró. Actualiza la página e inténtalo de nuevo.']);
        $this->redirect($redirectPath);
    }

    private function redirect(string $path): void
    {
        header('Location: ' . Url::to($path));
        exit;
    }
}
