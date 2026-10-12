<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function url(string $path): string
    {
        return self::e(Url::to($path));
    }

    /**
     * Renderiza app/Views/{$view}.php dentro de un layout.
     *
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], string $layout = 'admin/layout'): void
    {
        $viewsPath = dirname(__DIR__) . '/Views/';
        extract($data, EXTR_SKIP);

        ob_start();
        require $viewsPath . $view . '.php';
        $content = (string) ob_get_clean();

        require $viewsPath . $layout . '.php';
    }

    /** Fecha 'Y-m-d' o 'Y-m-d H:i:s' → d/m/Y */
    public static function fecha(?string $value, bool $conHora = false): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        $time = strtotime($value);

        return $time === false ? '—' : date($conHora ? 'd/m/Y H:i' : 'd/m/Y', $time);
    }

    public static function soles(float|int|string|null $amount): string
    {
        return 'S/ ' . number_format((float) $amount, 2, '.', ',');
    }

    /** Enlaces "Anterior · 1 · 2 · Siguiente" conservando los filtros de la query string. */
    public static function paginacion(Paginator $pag, string $ruta): string
    {
        if ($pag->pages <= 1) {
            return '';
        }
        $params = $_GET;
        unset($params['page']);
        $href = static fn (int $p): string => self::e(Url::to($ruta) . '?' . http_build_query($params + ['page' => $p]));

        $html = '<nav class="adm-pag" aria-label="Paginación">';
        $html .= $pag->page > 1
            ? '<a href="' . $href($pag->page - 1) . '">Anterior</a>'
            : '<span class="disabled">Anterior</span>';
        for ($p = 1; $p <= $pag->pages; $p++) {
            if ($p === 1 || $p === $pag->pages || abs($p - $pag->page) <= 2) {
                $html .= $p === $pag->page
                    ? '<span class="current" aria-current="page">' . $p . '</span>'
                    : '<a href="' . $href($p) . '">' . $p . '</a>';
            } elseif (abs($p - $pag->page) === 3) {
                $html .= '<span class="gap">…</span>';
            }
        }
        $html .= $pag->page < $pag->pages
            ? '<a href="' . $href($pag->page + 1) . '">Siguiente</a>'
            : '<span class="disabled">Siguiente</span>';

        return $html . '</nav>';
    }

    /** Etiqueta de color para un estado/rol. */
    public static function badge(string $valor, ?string $texto = null): string
    {
        $clase = match ($valor) {
            'activo', 'pagada', 'confirmada', 'devuelto', 'estudiante' => 'ok',
            'pendiente', 'bibliotecario' => 'warn',
            'vencido', 'bloqueado', 'expirada' => 'bad',
            'administrador' => 'brand',
            default => 'muted',
        };

        return '<span class="adm-badge adm-badge-' . $clase . '">' . self::e($texto ?? ucfirst($valor)) . '</span>';
    }
}
