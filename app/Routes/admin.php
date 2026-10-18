<?php
declare(strict_types=1);

/**
 * Rutas del módulo Administrador. Se incluye desde index.php; aquí $router ya existe.
 * Todas exigen rol "administrador" (lo valida BaseController en cada petición).
 * El Router solo admite rutas exactas, por eso los ids viajan en ?id= (GET) o en el cuerpo (POST).
 */

use App\Controllers\Admin\CategoriaController;
use App\Controllers\Admin\ConfiguracionController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\LibroController;
use App\Controllers\Admin\MultaController;
use App\Controllers\Admin\PrestamoController;
use App\Controllers\Admin\ReservaController;
use App\Controllers\Admin\UsuarioController;

/** @var App\Core\Router $router */

$router->get('/admin', static fn () => (new DashboardController())->index());
$router->get('/admin/configuracion', static fn () => (new ConfiguracionController())->index());
$router->post('/admin/configuracion/guardar', static fn () => (new ConfiguracionController())->guardar());

$router->get('/admin/libros', static fn () => (new LibroController())->index());
$router->get('/admin/libros/crear', static fn () => (new LibroController())->crear());
$router->get('/admin/libros/editar', static fn () => (new LibroController())->editar());
$router->post('/admin/libros/guardar', static fn () => (new LibroController())->guardar());
$router->post('/admin/libros/eliminar', static fn () => (new LibroController())->eliminar());

$router->get('/admin/categorias', static fn () => (new CategoriaController())->index());
$router->get('/admin/categorias/crear', static fn () => (new CategoriaController())->crear());
$router->get('/admin/categorias/editar', static fn () => (new CategoriaController())->editar());
$router->post('/admin/categorias/guardar', static fn () => (new CategoriaController())->guardar());
$router->post('/admin/categorias/eliminar', static fn () => (new CategoriaController())->eliminar());

$router->get('/admin/usuarios', static fn () => (new UsuarioController())->index());
$router->get('/admin/usuarios/crear', static fn () => (new UsuarioController())->crear());
$router->get('/admin/usuarios/editar', static fn () => (new UsuarioController())->editar());
$router->post('/admin/usuarios/guardar', static fn () => (new UsuarioController())->guardar());
$router->post('/admin/usuarios/eliminar', static fn () => (new UsuarioController())->eliminar());

$router->get('/admin/prestamos', static fn () => (new PrestamoController())->index());
$router->get('/admin/prestamos/crear', static fn () => (new PrestamoController())->crear());
$router->post('/admin/prestamos/guardar', static fn () => (new PrestamoController())->guardar());
$router->post('/admin/prestamos/devolver', static fn () => (new PrestamoController())->devolver());

$router->get('/admin/reservas', static fn () => (new ReservaController())->index());
$router->post('/admin/reservas/confirmar', static fn () => (new ReservaController())->confirmar());
$router->post('/admin/reservas/cancelar', static fn () => (new ReservaController())->cancelar());

$router->get('/admin/multas', static fn () => (new MultaController())->index());
$router->post('/admin/multas/pagar', static fn () => (new MultaController())->pagar());
$router->post('/admin/multas/condonar', static fn () => (new MultaController())->condonar());
