<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Flash;
use App\Core\Paginator;
use App\Models\Categoria;
use PDOException;

final class CategoriaController extends BaseController
{
    private Categoria $categorias;

    public function __construct()
    {
        parent::__construct();
        $this->categorias = new Categoria();
    }

    public function index(): void
    {
        $q = $this->busqueda();
        $pag = new Paginator($this->categorias->contar($q), self::POR_PAGINA, $this->pagina());

        $this->render('admin/categorias/index', 'categorias', 'Categorías', [
            'filas' => $this->categorias->listar($q, $pag->perPage, $pag->offset),
            'pag' => $pag,
            'q' => $q,
        ]);
    }

    public function crear(): void
    {
        $this->formulario(null, ['nombre' => '', 'descripcion' => '']);
    }

    public function editar(): void
    {
        $categoria = $this->categorias->buscar($this->idDe($_GET));
        if ($categoria === null) {
            Flash::error('La categoría no existe.');
            $this->redirigir('/admin/categorias');
        }
        $this->formulario($categoria, $categoria);
    }

    public function guardar(): void
    {
        $this->validarCsrf('/admin/categorias');
        $id = $this->idDe($_POST);
        $datos = ['nombre' => $this->texto('nombre'), 'descripcion' => $this->texto('descripcion')];

        $error = null;
        if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 100) {
            $error = 'El nombre es obligatorio (máximo 100 caracteres).';
        } elseif ($this->categorias->nombreExiste($datos['nombre'], $id)) {
            $error = 'Ya existe una categoría con ese nombre.';
        } elseif (mb_strlen($datos['descripcion']) > 2000) {
            $error = 'La descripción es demasiado larga.';
        }

        if ($error !== null) {
            http_response_code(422);
            $categoria = $id > 0 ? $this->categorias->buscar($id) : null;
            $this->formulario($categoria, $datos, $error);

            return;
        }

        $descripcion = $datos['descripcion'] === '' ? null : $datos['descripcion'];
        if ($id > 0) {
            if ($this->categorias->buscar($id) === null) {
                Flash::error('La categoría no existe.');
                $this->redirigir('/admin/categorias');
            }
            $this->categorias->actualizar($id, $datos['nombre'], $descripcion);
            Flash::success('Categoría actualizada.');
        } else {
            $this->categorias->crear($datos['nombre'], $descripcion);
            Flash::success('Categoría creada.');
        }
        $this->redirigir('/admin/categorias');
    }

    public function eliminar(): void
    {
        $this->validarCsrf('/admin/categorias');
        $id = $this->idDe($_POST);

        if ($this->categorias->buscar($id) === null) {
            Flash::error('La categoría no existe.');
        } elseif (($n = $this->categorias->contarLibros($id)) > 0) {
            Flash::error("No se puede eliminar: tiene $n libro(s) asociado(s). Reasígnalos primero.");
        } else {
            try {
                $this->categorias->eliminar($id);
                Flash::success('Categoría eliminada.');
            } catch (PDOException) {
                Flash::error('No se pudo eliminar la categoría.');
            }
        }
        $this->redirigir('/admin/categorias');
    }

    /** @param array<string, mixed>|null $categoria @param array<string, mixed> $valores */
    private function formulario(?array $categoria, array $valores, ?string $error = null): void
    {
        $this->render('admin/categorias/form', 'categorias', $categoria ? 'Editar categoría' : 'Nueva categoría', [
            'categoria' => $categoria,
            'v' => $valores,
            'error' => $error,
        ]);
    }
}
