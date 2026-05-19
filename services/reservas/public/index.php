<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Reservas\Router;
use Reservas\Controllers\ReservaController;

$router = new Router();

// Registrar rutas
$router->addRoute('GET', '/reservas/cliente/{id}',  [ReservaController::class, 'porCliente']);
$router->addRoute('GET', '/reservas/vehiculo/{id}', [ReservaController::class, 'porVehiculo']);
$router->addRoute('GET',    '/reservas',      [ReservaController::class, 'index']);
$router->addRoute('GET',    '/reservas/{id}', [ReservaController::class, 'show']);
$router->addRoute('POST',   '/reservas',      [ReservaController::class, 'store']);
$router->addRoute('PUT',    '/reservas/{id}', [ReservaController::class, 'update']);
$router->addRoute('DELETE', '/reservas/{id}', [ReservaController::class, 'destroy']);

$router->dispatch();
