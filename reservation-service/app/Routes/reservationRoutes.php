<?php

declare(strict_types=1);

use App\Controllers\DevolucionController;
use App\Controllers\ReservationController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {

    $app->get('/health', function ($request, $response): \Psr\Http\Message\ResponseInterface {
        $response->getBody()->write(json_encode([
            'status'    => 'ok',
            'service'   => 'reservation-service',
            'version'   => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json; charset=UTF-8');
    });

    // ── Reservas ─────────────────────────────────────────────────────────────
    $app->group('/api/reservations', function (RouteCollectorProxy $group): void {

        $group->get('',                              [ReservationController::class, 'index']);
        $group->get('/cliente/{clienteId:[0-9]+}',   [ReservationController::class, 'byCliente']);
        $group->get('/vehiculo/{vehiculoId:[0-9]+}', [ReservationController::class, 'byVehiculo']);
        $group->get('/estado/{estado}',              [ReservationController::class, 'byEstado']);
        $group->get('/{id:[0-9]+}',                  [ReservationController::class, 'show']);
        $group->post('',                             [ReservationController::class, 'store']);
        $group->put('/{id:[0-9]+}',                  [ReservationController::class, 'update']);
        $group->patch('/{id:[0-9]+}',                [ReservationController::class, 'update']);
        $group->delete('/{id:[0-9]+}',               [ReservationController::class, 'destroy']);
        $group->patch('/{id:[0-9]+}/cancelar',       [ReservationController::class, 'cancel']);
        $group->patch('/{id:[0-9]+}/completar',      [ReservationController::class, 'complete']);
    });

    // ── Devoluciones ──────────────────────────────────────────────────────────
    $app->group('/api/devoluciones', function (RouteCollectorProxy $group): void {

        $group->get('',               [DevolucionController::class, 'index']);
        $group->get('/{id:[0-9]+}',   [DevolucionController::class, 'show']);
        $group->post('',              [DevolucionController::class, 'store']);
        $group->delete('/{id:[0-9]+}',[DevolucionController::class, 'destroy']);
    });
};
