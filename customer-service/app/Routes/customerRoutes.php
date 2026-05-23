<?php

declare(strict_types=1);

use App\Controllers\CustomerController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {

    // Health check del microservicio
    $app->get('/health', function ($request, $response): \Psr\Http\Message\ResponseInterface {
        $response->getBody()->write(json_encode([
            'status'    => 'ok',
            'service'   => 'customer-service',
            'version'   => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json; charset=UTF-8');
    });

    // Rutas del recurso /api/customers
    $app->group('/api/customers', function (RouteCollectorProxy $group): void {

        // GET /api/customers - Listar todos los clientes
        $group->get('', [CustomerController::class, 'index']);

        // GET /api/customers/search?q=texto - Buscar clientes
        $group->get('/search', [CustomerController::class, 'search']);

        // GET /api/customers/{id} - Obtener un cliente
        $group->get('/{id:[0-9]+}', [CustomerController::class, 'show']);

        // POST /api/customers - Registrar cliente
        $group->post('', [CustomerController::class, 'store']);

        // PUT /api/customers/{id} - Actualización completa
        $group->put('/{id:[0-9]+}', [CustomerController::class, 'update']);

        // PATCH /api/customers/{id} - Actualización parcial
        $group->patch('/{id:[0-9]+}', [CustomerController::class, 'update']);

        // DELETE /api/customers/{id} - Eliminar cliente
        $group->delete('/{id:[0-9]+}', [CustomerController::class, 'destroy']);
    });
};
