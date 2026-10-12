<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(string $method, string $requestUri): void
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        if (!is_string($path)) {
            $this->notFound();
            return;
        }

        $basePath = Url::basePath();
        if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
            $path = substr($path, strlen($basePath));
        }
        $path = '/' . trim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        $handler = $this->routes[strtoupper($method)][$path] ?? null;
        if ($handler === null) {
            $this->notFound();
            return;
        }

        $handler();
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $normalizedPath = '/' . trim($path, '/');
        if ($normalizedPath === '//') {
            $normalizedPath = '/';
        }
        $this->routes[$method][$normalizedPath] = $handler;
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo 'Página no encontrada.';
    }
}
