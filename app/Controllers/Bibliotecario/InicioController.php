<?php
declare(strict_types=1);

namespace App\Controllers\Bibliotecario;

use App\Core\Csrf;

final class InicioController extends BaseController
{
    public function index(): void
    {
        $csrfToken = Csrf::token();
        require dirname(__DIR__, 2) . '/Views/home/index.php';
    }
}
