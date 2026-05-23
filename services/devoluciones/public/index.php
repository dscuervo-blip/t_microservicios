<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Devoluciones\Router;
use Devoluciones\Controllers\DevolucionController;

$router = new Router();

// Registrar rutas
$router->addRoute('GET', '/devoluciones/reserva/{id}', [DevolucionController::class, 'porReserva']);
$router->addRoute('GET',    '/devoluciones',      [DevolucionController::class, 'index']);
$router->addRoute('GET',    '/devoluciones/{id}', [DevolucionController::class, 'show']);
$router->addRoute('POST',   '/devoluciones',      [DevolucionController::class, 'store']);
$router->addRoute('DELETE', '/devoluciones/{id}', [DevolucionController::class, 'destroy']);

$router->dispatch();
