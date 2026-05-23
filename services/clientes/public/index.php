<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Clientes\Router;
use Clientes\Controllers\ClienteController;

$router = new Router();

// Registrar rutas
$router->addRoute('GET',    '/clientes',      [ClienteController::class, 'index']);
$router->addRoute('GET',    '/clientes/{id}', [ClienteController::class, 'show']);
$router->addRoute('POST',   '/clientes',      [ClienteController::class, 'store']);
$router->addRoute('PUT',    '/clientes/{id}', [ClienteController::class, 'update']);
$router->addRoute('DELETE', '/clientes/{id}', [ClienteController::class, 'destroy']);

$router->dispatch();
