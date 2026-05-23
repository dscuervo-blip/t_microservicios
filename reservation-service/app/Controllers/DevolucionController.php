<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DevolucionService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

class DevolucionController
{
    public function __construct(private readonly DevolucionService $service) {}

    public function index(Request $request, Response $response): Response
    {
        try {
            $items = $this->service->getAllDevoluciones();
            return $this->json($response, [
                'status' => 'success',
                'total'  => count($items),
                'data'   => $items,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $item = $this->service->getDevolucionById((int) $args['id']);
            return $this->json($response, ['status' => 'success', 'data' => $item]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $data = (array) $request->getParsedBody();
            $item = $this->service->createDevolucion($data);
            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Devolución registrada. Reserva completada y vehículo liberado.',
                'data'    => $item,
            ], 201);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, ['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $this->service->deleteDevolucion((int) $args['id']);
            return $this->json($response, ['status' => 'success', 'message' => 'Devolución eliminada.']);
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
        $code       = $e->getCode();
        $httpStatus = ($code >= 400 && $code < 600) ? (int) $code : 500;
        return $this->json($response, ['status' => 'error', 'message' => $e->getMessage()], $httpStatus);
    }
}
