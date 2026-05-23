<?php

declare(strict_types=1);

use App\Controllers\VehicleController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {

    // Health check del microservicio
    $app->get('/health', function ($request, $response): \Psr\Http\Message\ResponseInterface {
        $response->getBody()->write(json_encode([
            'status'    => 'ok',
            'service'   => 'vehicle-service',
            'version'   => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json; charset=UTF-8');
    });

    // Rutas del recurso /api/vehicles
    $app->group('/api/vehicles', function (RouteCollectorProxy $group): void {

        // GET /api/vehicles - Listar todos los vehículos
        $group->get('', [VehicleController::class, 'index']);

        // GET /api/vehicles/disponibles - Listar vehículos disponibles
        $group->get('/disponibles', [VehicleController::class, 'available']);

        // GET /api/vehicles/categoria/{categoria} - Filtrar por categoría
        $group->get('/categoria/{categoria}', [VehicleController::class, 'byCategory']);

        // GET /api/vehicles/{id} - Obtener un vehículo
        $group->get('/{id:[0-9]+}', [VehicleController::class, 'show']);

        // POST /api/vehicles - Crear vehículo
        $group->post('', [VehicleController::class, 'store']);

        // PUT /api/vehicles/{id} - Actualización completa
        $group->put('/{id:[0-9]+}', [VehicleController::class, 'update']);

        // PATCH /api/vehicles/{id} - Actualización parcial
        $group->patch('/{id:[0-9]+}', [VehicleController::class, 'update']);

        // DELETE /api/vehicles/{id} - Eliminar vehículo
        $group->delete('/{id:[0-9]+}', [VehicleController::class, 'destroy']);
    });
};
