<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Vehiculos\Router;
use Vehiculos\Controllers\VehiculoController;

$router = new Router();

// Registrar rutas
$router->addRoute('GET',    '/vehiculos/disponibles', [VehiculoController::class, 'disponibles']);
$router->addRoute('GET',    '/vehiculos',             [VehiculoController::class, 'index']);
$router->addRoute('GET',    '/vehiculos/{id}',        [VehiculoController::class, 'show']);
$router->addRoute('POST',   '/vehiculos',             [VehiculoController::class, 'store']);
$router->addRoute('PUT',    '/vehiculos/{id}',        [VehiculoController::class, 'update']);
$router->addRoute('DELETE', '/vehiculos/{id}',        [VehiculoController::class, 'destroy']);

$router->dispatch();
