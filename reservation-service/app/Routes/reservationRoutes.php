<?php

declare(strict_types=1);

use App\Controllers\ReservationController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {

    // Health check del microservicio
    $app->get('/health', function ($request, $response): \Psr\Http\Message\ResponseInterface {
        $response->getBody()->write(json_encode([
            'status'    => 'ok',
            'service'   => 'reservation-service',
            'version'   => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json; charset=UTF-8');
    });

    // Rutas del recurso /api/reservations
    $app->group('/api/reservations', function (RouteCollectorProxy $group): void {

        // ── Listados ──────────────────────────────────────────────────────────

        // GET /api/reservations - Todas las reservas (con JOIN a clientes y vehiculos)
        $group->get('', [ReservationController::class, 'index']);

        // GET /api/reservations/cliente/{clienteId}
        $group->get('/cliente/{clienteId:[0-9]+}', [ReservationController::class, 'byCliente']);

        // GET /api/reservations/vehiculo/{vehiculoId}
        $group->get('/vehiculo/{vehiculoId:[0-9]+}', [ReservationController::class, 'byVehiculo']);

        // GET /api/reservations/estado/{estado} (activa | completada | cancelada)
        $group->get('/estado/{estado}', [ReservationController::class, 'byEstado']);

        // GET /api/reservations/{id}
        $group->get('/{id:[0-9]+}', [ReservationController::class, 'show']);

        // ── Creación y edición ────────────────────────────────────────────────

        // POST /api/reservations  →  crea reserva (estado: activa) + vehículo → alquilado
        $group->post('', [ReservationController::class, 'store']);

        // PUT  /api/reservations/{id}
        $group->put('/{id:[0-9]+}', [ReservationController::class, 'update']);

        // PATCH /api/reservations/{id}
        $group->patch('/{id:[0-9]+}', [ReservationController::class, 'update']);

        // DELETE /api/reservations/{id}  (solo si cancelada o completada)
        $group->delete('/{id:[0-9]+}', [ReservationController::class, 'destroy']);

        // ── Transiciones de estado ────────────────────────────────────────────

        // PATCH /api/reservations/{id}/cancelar  →  activa → cancelada + vehículo → disponible
        $group->patch('/{id:[0-9]+}/cancelar', [ReservationController::class, 'cancel']);

        // PATCH /api/reservations/{id}/completar →  activa → completada + vehículo → disponible
        $group->patch('/{id:[0-9]+}/completar', [ReservationController::class, 'complete']);
    });
};
