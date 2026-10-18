<?php
declare(strict_types=1);

namespace App\Controllers\Bibliotecario;

use App\Core\Paginator;
use App\Models\Libro;

final class LibroController extends BaseController
{
    public function index(): void
    {
        $libros = new Libro();
        $q = $this->busqueda();
        $pag = new Paginator($libros->contar($q), self::POR_PAGINA, $this->pagina());
        $this->render('bibliotecario/libros/index', 'libros', 'Catálogo de libros', [
            'filas' => $libros->listar($q, 0, $pag->perPage, $pag->offset),
            'pag' => $pag,
            'q' => $q,
        ]);
    }
}
