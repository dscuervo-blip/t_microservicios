<?php

namespace Gateway;

class Gateway
{
    private const BASE = '/t_microservicios/gateway/public';

    private array $services = [
        'vehiculos'    => [
            'url'    => 'http://localhost/t_microservicios/vehicle-service/public',
            'prefix' => '/api/vehicles',
        ],
        'clientes'     => [
            'url'    => 'http://localhost/t_microservicios/customer-service/public',
            'prefix' => '/api/customers',
        ],
        'reservas'     => [
            'url'    => 'http://localhost/t_microservicios/reservation-service/public',
            'prefix' => '/api/reservations',
        ],
        'devoluciones' => [
            'url'    => 'http://localhost/t_microservicios/reservation-service/public',
            'prefix' => '/api/devoluciones',
        ],
    ];

    public function handle(): void
    {
        $this->addCorsHeaders();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            return;
        }

        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path   = ltrim(str_replace(self::BASE, '', $uri), '/');
        $parts  = explode('/', $path, 2);
        $key    = $parts[0] ?? '';

        if (!array_key_exists($key, $this->services)) {
            $this->respond(404, ['error' => "Servicio '$key' no encontrado"]);
            return;
        }

        $service  = $this->services[$key];
        $subpath  = isset($parts[1]) && $parts[1] !== '' ? '/' . $parts[1] : '';
        $query    = $_SERVER['QUERY_STRING'] ?? '';
        $url      = $service['url'] . $service['prefix'] . $subpath . ($query ? "?$query" : '');

        $this->proxy($url);
    }

    private function proxy(string $url): void
    {
        $method  = $_SERVER['REQUEST_METHOD'];
        $body    = file_get_contents('php://input');
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            $this->respond(502, ['error' => 'Servicio no disponible', 'detalle' => $error]);
            return;
        }

        http_response_code($status ?: 500);
        header('Content-Type: application/json; charset=utf-8');
        echo $response;
    }

    private function addCorsHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }

    private function respond(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
