<?php
declare(strict_types=1);

namespace App\Controllers\Bibliotecario;

use App\Core\Flash;
use App\Core\Paginator;
use App\Models\Multa;
use App\Services\Circulacion;
use DomainException;

final class MultaController extends BaseController
{
    public function index(): void
    {
        (new Circulacion())->actualizarEstados();

        $multas = new Multa();
        $q = $this->busqueda();
        $estado = $this->texto('estado', $_GET);
        $pag = new Paginator($multas->contar($estado, $q), self::POR_PAGINA, $this->pagina());
        $this->render('bibliotecario/multas/index', 'multas', 'Multas', [
            'filas' => $multas->listar($estado, $q, $pag->perPage, $pag->offset),
            'resumen' => $multas->resumen(),
            'pag' => $pag,
            'q' => $q,
            'estado' => $estado,
            'estados' => Multa::ESTADOS,
        ]);
    }

    public function pagar(): void
    {
        $this->validarCsrf('/bibliotecario/multas');
        try {
            (new Circulacion())->pagarMulta($this->idPost());
            Flash::success('Pago registrado: la multa quedó como pagada.');
        } catch (DomainException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirigir('/bibliotecario/multas');
    }
}
