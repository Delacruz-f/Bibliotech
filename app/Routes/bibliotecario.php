<?php
declare(strict_types=1);

use App\Controllers\Bibliotecario\LibroController;
use App\Controllers\Bibliotecario\InicioController;
use App\Controllers\Bibliotecario\MultaController;
use App\Controllers\Bibliotecario\PrestamoController;
use App\Controllers\Bibliotecario\ReservaController;

/** @var App\Core\Router $router */

$router->get('/bibliotecario', static fn () => (new InicioController())->index());
$router->get('/bibliotecario/libros', static fn () => (new LibroController())->index());
$router->get('/bibliotecario/prestamos', static fn () => (new PrestamoController())->index());
$router->get('/bibliotecario/prestamos/crear', static fn () => (new PrestamoController())->crear());
$router->post('/bibliotecario/prestamos/guardar', static fn () => (new PrestamoController())->guardar());
$router->post('/bibliotecario/prestamos/devolver', static fn () => (new PrestamoController())->devolver());
$router->get('/bibliotecario/reservas', static fn () => (new ReservaController())->index());
$router->post('/bibliotecario/reservas/confirmar', static fn () => (new ReservaController())->confirmar());
$router->get('/bibliotecario/multas', static fn () => (new MultaController())->index());
$router->post('/bibliotecario/multas/pagar', static fn () => (new MultaController())->pagar());
