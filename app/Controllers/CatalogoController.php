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
        $this->requireStudent();
        $requestedPage = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
        $pageData = (new Libro())->obtenerTodos(max(1, $requestedPage === false || $requestedPage === null ? 1 : $requestedPage));
        $flash = Flash::consume();
        $csrfToken = Csrf::token();
        $pageTitle = 'Catálogo de libros';

        require dirname(__DIR__) . '/Views/estudiante/catalogo.php';
    }

    private function requireStudent(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!is_array($user) || !isset($user['id']) || (int) $user['id'] < 1) {
            header('Location: ' . Url::to('/login'));
            exit;
        }

        if (($user['rol'] ?? '') !== 'estudiante') {
            header('Location: ' . Url::to('/home'));
            exit;
        }
    }
}
