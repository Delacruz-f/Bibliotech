<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Url;
use App\Models\Multa;

final class MultaController
{
    public function index(): void
    {
        $idUsuario = $this->requireStudent();
        $multas = (new Multa())->obtenerPorUsuario($idUsuario);
        $summary = [
            'total' => count($multas),
            'pendientes' => 0,
            'pagadas' => 0,
            'monto_pendiente' => 0.0,
        ];

        foreach ($multas as $multa) {
            if ($this->isPaid($multa['estado'] ?? '')) {
                $summary['pagadas']++;
            } elseif ($this->isPending($multa['estado'] ?? '')) {
                $summary['pendientes']++;
                $summary['monto_pendiente'] += (float) ($multa['monto'] ?? 0);
            }
        }

        $flash = Flash::consume();
        $csrfToken = Csrf::token();
        $pageTitle = 'Mis multas';

        require dirname(__DIR__) . '/Views/estudiante/mis_multas.php';
    }

    public function pagar(): void
    {
        $idUsuario = $this->requireStudent();
        if (!Csrf::isValid($_POST['_csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set(['error' => 'La solicitud expiró. Actualiza la página e inténtalo de nuevo.']);
        } else {
            $idMulta = filter_var($_POST['id_multa'] ?? null, FILTER_VALIDATE_INT);
            if (!is_int($idMulta) || $idMulta < 1) {
                Flash::set(['error' => 'No se recibió una multa válida.']);
            } elseif ((new Multa())->pagar($idMulta, $idUsuario)) {
                Flash::set(['success' => 'La multa fue marcada como pagada.']);
            } else {
                Flash::set([
                    'error' => 'No se pudo pagar la multa. Puede que no te pertenezca o ya no esté pendiente.',
                ]);
            }
        }

        $this->redirect('/mis-multas');
    }

    private function isPaid(mixed $status): bool
    {
        return strtolower(trim((string) $status)) === 'pagada';
    }

    private function isPending(mixed $status): bool
    {
        return strtolower(trim((string) $status)) === 'pendiente';
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

    private function redirect(string $path): void
    {
        header('Location: ' . Url::to($path));
        exit;
    }
}
