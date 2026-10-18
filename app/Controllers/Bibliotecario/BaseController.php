<?php
declare(strict_types=1);

namespace App\Controllers\Bibliotecario;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Url;
use App\Core\View;

abstract class BaseController
{
    protected const POR_PAGINA = 10;

    /** @var array<string, mixed> */
    protected array $usuario;

    public function __construct()
    {
        $this->usuario = Auth::requireRole(['bibliotecario']);
        if (($_SESSION['user']['rol'] ?? null) !== 'bibliotecario') {
            Auth::redirect('/home');
        }
    }

    /** @param array<string, mixed> $data */
    protected function render(string $vista, string $seccion, string $titulo, array $data = []): void
    {
        View::render($vista, $data + [
            'seccion' => $seccion,
            'titulo' => $titulo,
            'usuario' => $this->usuario,
            'csrf' => Csrf::token(),
            'mensajes' => Flash::pull(),
        ], 'bibliotecario/layout');
    }

    protected function redirigir(string $ruta): never
    {
        header('Location: ' . Url::to($ruta));
        exit;
    }

    protected function validarCsrf(string $volverA): void
    {
        if (!Csrf::isValid($_POST['_csrf_token'] ?? null)) {
            Flash::error('La sesión expiró. Vuelve a intentarlo.');
            $this->redirigir($volverA);
        }
    }

    protected function idPost(): int
    {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        return $id === false || $id === null || $id < 1 ? 0 : $id;
    }

    protected function texto(string $clave, ?array $origen = null): string
    {
        $origen ??= $_POST;
        $valor = $origen[$clave] ?? '';

        return is_string($valor) ? trim($valor) : '';
    }

    protected function busqueda(): string
    {
        return mb_substr($this->texto('q', $_GET), 0, 100);
    }

    protected function pagina(): int
    {
        $pagina = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);

        return $pagina === false || $pagina === null ? 1 : max(1, $pagina);
    }
}
