<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\VehicleService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Throwable;

class VehicleController
{
    public function __construct(private readonly VehicleService $service) {}

    public function index(Request $request, Response $response): Response
    {
        try {
            $vehicles = $this->service->getAllVehicles();

            return $this->json($response, [
                'status' => 'success',
                'total'  => count($vehicles),
                'data'   => $vehicles,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $vehicle = $this->service->getVehicleById((int) $args['id']);

            return $this->json($response, [
                'status' => 'success',
                'data'   => $vehicle,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function available(Request $request, Response $response): Response
    {
        try {
            $vehicles = $this->service->getAvailableVehicles();

            return $this->json($response, [
                'status' => 'success',
                'total'  => count($vehicles),
                'data'   => $vehicles,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function byCategory(Request $request, Response $response, array $args): Response
    {
        try {
            $vehicles = $this->service->getVehiclesByCategory($args['categoria']);

            return $this->json($response, [
                'status'    => 'success',
                'categoria' => $args['categoria'],
                'total'     => count($vehicles),
                'data'      => $vehicles,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $data    = (array) $request->getParsedBody();
            $vehicle = $this->service->createVehicle($data);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Vehículo creado exitosamente.',
                'data'    => $vehicle,
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
            $data    = (array) $request->getParsedBody();
            $vehicle = $this->service->updateVehicle((int) $args['id'], $data);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Vehículo actualizado exitosamente.',
                'data'    => $vehicle,
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
            $this->service->deleteVehicle((int) $args['id']);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Vehículo eliminado exitosamente.',
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

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
        $code = $e->getCode();
        $httpStatus = ($code >= 400 && $code < 600) ? (int) $code : 500;

        return $this->json($response, [
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], $httpStatus);
    }
}
