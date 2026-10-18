<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Url;
use App\Models\Libro;

final class CatalogoController
{
    public function index(): void
    {
        $requestedPage = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
        $pageData = (new Libro())->obtenerTodos(max(1, $requestedPage === false || $requestedPage === null ? 1 : $requestedPage));
        $flash = Flash::consume();
        $csrfToken = Csrf::token();
        $canReserve = $this->isStudent();
        $pageTitle = 'Catálogo de libros';

        require dirname(__DIR__) . '/Views/estudiante/catalogo.php';
    }

    public function show(): void
    {
        $requestedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $bookId = $requestedId === false || $requestedId === null ? 0 : $requestedId;
        $libro = $bookId > 0 ? (new Libro())->detalle($bookId) : null;
        if ($libro === null) {
            http_response_code(404);
        }

        $canReserve = $this->isStudent();
        $csrfToken = Csrf::token();
        $pageTitle = $libro === null ? 'Libro no encontrado' : (string) $libro['titulo'];

        require dirname(__DIR__) . '/Views/estudiante/libro.php';
    }

    private function isStudent(): bool
    {
        $user = $_SESSION['user'] ?? null;

        return is_array($user)
            && isset($user['id'])
            && (int) $user['id'] > 0
            && ($user['rol'] ?? '') === 'estudiante';
    }
}
