<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

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

        $this->render('admin/multas/index', 'multas', 'Multas', [
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
        $this->accion(static fn (Circulacion $c, int $id) => $c->pagarMulta($id), 'Pago registrado: la multa quedó como pagada.');
    }

    public function condonar(): void
    {
        $this->accion(static fn (Circulacion $c, int $id) => $c->condonarMulta($id), 'Multa condonada.');
    }

    private function accion(callable $accion, string $exito): void
    {
        $this->validarCsrf('/admin/multas');
        try {
            $accion(new Circulacion(), $this->idDe($_POST));
            Flash::success($exito);
        } catch (DomainException $e) {
            Flash::error($e->getMessage());
        }
        $this->redirigir('/admin/multas');
    }
}
