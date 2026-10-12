<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Flash;
use App\Core\Paginator;
use App\Models\Categoria;
use App\Models\Libro;
use PDOException;

final class LibroController extends BaseController
{
    private Libro $libros;
    private Categoria $categorias;

    public function __construct()
    {
        parent::__construct();
        $this->libros = new Libro();
        $this->categorias = new Categoria();
    }

    public function index(): void
    {
        $q = $this->busqueda();
        $cat = $this->idDe($_GET, 'categoria');
        $pag = new Paginator($this->libros->contar($q, $cat), self::POR_PAGINA, $this->pagina());

        $this->render('admin/libros/index', 'libros', 'Libros', [
            'filas' => $this->libros->listar($q, $cat, $pag->perPage, $pag->offset),
            'categorias' => $this->categorias->todas(),
            'pag' => $pag,
            'q' => $q,
            'cat' => $cat,
        ]);
    }

    public function crear(): void
    {
        $this->formulario(null, [
            'titulo' => '', 'autor' => '', 'isbn' => '', 'id_categoria' => '', 'editorial' => '',
            'anio' => '', 'portada_url' => '', 'sinopsis' => '', 'stock_total' => '1',
        ]);
    }

    public function editar(): void
    {
        $libro = $this->libros->buscar($this->idDe($_GET));
        if ($libro === null) {
            Flash::error('El libro no existe.');
            $this->redirigir('/admin/libros');
        }
        $this->formulario($libro, $libro);
    }

    public function guardar(): void
    {
        $this->validarCsrf('/admin/libros');
        $id = $this->idDe($_POST);
        $libro = $id > 0 ? $this->libros->buscar($id) : null;
        if ($id > 0 && $libro === null) {
            Flash::error('El libro no existe.');
            $this->redirigir('/admin/libros');
        }

        $v = [
            'titulo' => $this->texto('titulo'),
            'autor' => $this->texto('autor'),
            'isbn' => $this->texto('isbn'),
            'id_categoria' => $this->texto('id_categoria'),
            'editorial' => $this->texto('editorial'),
            'anio' => $this->texto('anio'),
            'portada_url' => $this->texto('portada_url'),
            'sinopsis' => $this->texto('sinopsis'),
            'stock_total' => $this->texto('stock_total'),
        ];

        $error = $this->validar($v, $id, $libro);
        if ($error !== null) {
            http_response_code(422);
            $this->formulario($libro, $v, $error);

            return;
        }

        $datos = [
            'titulo' => $v['titulo'],
            'autor' => $v['autor'],
            'isbn' => $v['isbn'],
            'id_categoria' => (int) $v['id_categoria'],
            'editorial' => $v['editorial'] === '' ? null : $v['editorial'],
            'anio' => $v['anio'] === '' ? null : (int) $v['anio'],
            'portada_url' => $v['portada_url'] === '' ? null : $v['portada_url'],
            'sinopsis' => $v['sinopsis'] === '' ? null : $v['sinopsis'],
            'stock_total' => (int) $v['stock_total'],
        ];

        if ($id > 0) {
            $this->libros->actualizar($id, $datos);
            Flash::success('Libro actualizado.');
        } else {
            $this->libros->crear($datos);
            Flash::success('Libro creado.');
        }
        $this->redirigir('/admin/libros');
    }

    public function eliminar(): void
    {
        $this->validarCsrf('/admin/libros');
        $id = $this->idDe($_POST);

        if ($this->libros->buscar($id) === null) {
            Flash::error('El libro no existe.');
        } elseif ($this->libros->tieneMovimientos($id)) {
            Flash::error('No se puede eliminar: el libro tiene préstamos o reservas registrados.');
        } else {
            try {
                $this->libros->eliminar($id);
                Flash::success('Libro eliminado.');
            } catch (PDOException) {
                Flash::error('No se pudo eliminar el libro.');
            }
        }
        $this->redirigir('/admin/libros');
    }

    /** @param array<string, mixed> $v @param array<string, mixed>|null $libro */
    private function validar(array $v, int $id, ?array $libro): ?string
    {
        if ($v['titulo'] === '' || mb_strlen($v['titulo']) > 255) {
            return 'El título es obligatorio (máximo 255 caracteres).';
        }
        if ($v['autor'] === '' || mb_strlen($v['autor']) > 180) {
            return 'El autor es obligatorio (máximo 180 caracteres).';
        }
        if ($v['isbn'] === '' || mb_strlen($v['isbn']) > 20) {
            return 'El ISBN es obligatorio (máximo 20 caracteres).';
        }
        if ($this->libros->isbnExiste($v['isbn'], $id)) {
            return 'Ya existe un libro con ese ISBN.';
        }
        if (!ctype_digit($v['id_categoria']) || $this->categorias->buscar((int) $v['id_categoria']) === null) {
            return 'Selecciona una categoría válida.';
        }
        if (mb_strlen($v['editorial']) > 150) {
            return 'La editorial admite hasta 150 caracteres.';
        }
        if ($v['anio'] !== '' && (!ctype_digit($v['anio']) || (int) $v['anio'] < 1000 || (int) $v['anio'] > (int) date('Y') + 1)) {
            return 'El año no es válido.';
        }
        if ($v['portada_url'] !== '') {
            if (mb_strlen($v['portada_url']) > 500 || filter_var($v['portada_url'], FILTER_VALIDATE_URL) === false
                || !preg_match('#^https?://#i', $v['portada_url'])) {
                return 'La portada debe ser una URL http(s) válida (máximo 500 caracteres).';
            }
        }
        if (mb_strlen($v['sinopsis']) > 5000) {
            return 'La sinopsis es demasiado larga (máximo 5000 caracteres).';
        }
        if (!ctype_digit($v['stock_total']) || (int) $v['stock_total'] > 9999) {
            return 'El stock total debe ser un número entero entre 0 y 9999.';
        }
        if ($libro !== null) {
            $prestados = (int) $libro['stock_total'] - (int) $libro['stock_disponible'];
            if ((int) $v['stock_total'] < $prestados) {
                return "El stock total no puede ser menor a los $prestados ejemplar(es) actualmente prestado(s).";
            }
        }

        return null;
    }

    /** @param array<string, mixed>|null $libro @param array<string, mixed> $valores */
    private function formulario(?array $libro, array $valores, ?string $error = null): void
    {
        $this->render('admin/libros/form', 'libros', $libro ? 'Editar libro' : 'Nuevo libro', [
            'libro' => $libro,
            'v' => $valores,
            'error' => $error,
            'categorias' => $this->categorias->todas(),
        ]);
    }
}
