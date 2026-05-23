<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CustomerService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

class CustomerController
{
    public function __construct(private readonly CustomerService $service) {}

    public function index(Request $request, Response $response): Response
    {
        try {
            $customers = $this->service->getAllCustomers();

            return $this->json($response, [
                'status' => 'success',
                'total'  => count($customers),
                'data'   => $customers,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $customer = $this->service->getCustomerById((int) $args['id']);

            return $this->json($response, [
                'status' => 'success',
                'data'   => $customer,
            ]);
        } catch (Throwable $e) {
            return $this->handleError($response, $e);
        }
    }

    public function search(Request $request, Response $response): Response
    {
        try {
            $params    = $request->getQueryParams();
            $query     = $params['q'] ?? '';
            $customers = $this->service->searchCustomers($query);

            return $this->json($response, [
                'status' => 'success',
                'query'  => $query,
                'total'  => count($customers),
                'data'   => $customers,
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

    public function store(Request $request, Response $response): Response
    {
        try {
            $data     = (array) $request->getParsedBody();
            $customer = $this->service->createCustomer($data);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Cliente registrado exitosamente.',
                'data'    => $customer,
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
            $data     = (array) $request->getParsedBody();
            $customer = $this->service->updateCustomer((int) $args['id'], $data);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Cliente actualizado exitosamente.',
                'data'    => $customer,
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
            $this->service->deleteCustomer((int) $args['id']);

            return $this->json($response, [
                'status'  => 'success',
                'message' => 'Cliente eliminado exitosamente.',
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
        $code       = $e->getCode();
        $httpStatus = ($code >= 400 && $code < 600) ? (int) $code : 500;

        return $this->json($response, [
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], $httpStatus);
    }
}
