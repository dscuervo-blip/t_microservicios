<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ReservationService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

class ReservationController
{
    public function __construct(private readonly ReservationService $service) {}

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    public function index(Request $request, Response $response): Response
    {
        try {
            $reservations = $this->service->getAllReservations();

            return $this->json($response, [
                'status' => 'success',
                'total'  => count($reservations),
                'data'   => $reservations,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $reservation = $this->service->getReservationById((int) $args['id']);

            return $this->json($response, [
                'status' => 'success',
                'data'   => $reservation,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $data        = (array) $request->getParsedBody();
            $reservation = $this->service->createReservation($data);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Reserva creada exitosamente. El vehículo ha sido marcado como alquilado.',
                'data'    => $reservation,
            ], 201);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $data        = (array) $request->getParsedBody();
            $reservation = $this->service->updateReservation((int) $args['id'], $data);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Reserva actualizada exitosamente.',
                'data'    => $reservation,
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $this->service->deleteReservation((int) $args['id']);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Reserva eliminada exitosamente.',
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    // ─── Filtros ──────────────────────────────────────────────────────────────

    public function byCliente(Request $request, Response $response, array $args): Response
    {
        try {
            $reservations = $this->service->getReservationsByCliente((int) $args['clienteId']);

            return $this->json($response, [
                'status'     => 'success',
                'cliente_id' => (int) $args['clienteId'],
                'total'      => count($reservations),
                'data'       => $reservations,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function byVehiculo(Request $request, Response $response, array $args): Response
    {
        try {
            $reservations = $this->service->getReservationsByVehiculo((int) $args['vehiculoId']);

            return $this->json($response, [
                'status'      => 'success',
                'vehiculo_id' => (int) $args['vehiculoId'],
                'total'       => count($reservations),
                'data'        => $reservations,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function byEstado(Request $request, Response $response, array $args): Response
    {
        try {
            $reservations = $this->service->getReservationsByEstado($args['estado']);

            return $this->json($response, [
                'status' => 'success',
                'estado' => $args['estado'],
                'total'  => count($reservations),
                'data'   => $reservations,
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    // ─── Transiciones de estado ───────────────────────────────────────────────

    public function cancel(Request $request, Response $response, array $args): Response
    {
        try {
            $reservation = $this->service->cancelReservation((int) $args['id']);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Reserva cancelada. El vehículo está nuevamente disponible.',
                'data'    => $reservation,
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function complete(Request $request, Response $response, array $args): Response
    {
        try {
            $reservation = $this->service->completeReservation((int) $args['id']);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Reserva completada. El vehículo está nuevamente disponible.',
                'data'    => $reservation,
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function json(Response $response, array $payload, int $status = 200): Response
    {
        $response->getBody()->write(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        return $response
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withStatus($status);
    }

    private function handleError(Response $response, Throwable $e): Response
    {
        $code       = $e->getCode();
        $httpStatus = ($code >= 400 && $code < 600) ? (int) $code : 500;

        return $this->json($response, [
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], $httpStatus);
    }
}
