<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

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
        $this->usuario = Auth::requireRole(['administrador']);
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
        ]);
    }

    protected function redirigir(string $ruta): never
    {
        header('Location: ' . Url::to($ruta));
        exit;
    }

    /** Aborta si el token CSRF del POST no es válido. */
    protected function validarCsrf(string $volverA): void
    {
        if (!Csrf::isValid($_POST['_csrf_token'] ?? null)) {
            Flash::error('La sesión expiró. Vuelve a intentarlo.');
            $this->redirigir($volverA);
        }
    }

    protected function idDe(array $origen, string $clave = 'id'): int
    {
        $valor = $origen[$clave] ?? 0;

        return is_scalar($valor) ? max(0, (int) $valor) : 0;
    }

    protected function texto(string $clave, ?array $origen = null): string
    {
        $origen ??= $_POST;
        $valor = $origen[$clave] ?? '';

        return is_string($valor) ? trim($valor) : '';
    }

    /** Búsqueda de la query string: texto recortado y acotado. */
    protected function busqueda(): string
    {
        return mb_substr($this->texto('q', $_GET), 0, 100);
    }

    protected function pagina(): int
    {
        return max(1, $this->idDe($_GET, 'page'));
    }
}
