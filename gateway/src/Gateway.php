<?php

namespace Gateway;

class Gateway
{

    private const BASE = '/t_microservicios/gateway/public';

    private array $services = [

        'vehiculos' =>
        'http://localhost/t_microservicios/services/vehiculos/public/index.php',

        'clientes' =>
        'http://localhost/t_microservicios/services/clientes/public/index.php',

        'reservas' =>
        'http://localhost/t_microservicios/services/reservas/public/index.php',

        'devoluciones' =>
        'http://localhost/t_microservicios/services/devoluciones/public/index.php',
    ];

    public function handle(): void
    {

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $path = ltrim(
            str_replace(self::BASE, '', $uri),
            '/'
        );

        $parts = explode('/', $path, 2);

        $service = $parts[0] ?? '';

        if (!isset($this->services[$service])) {

            $this->respond(404, [
                'error' => 'Servicio no encontrado'
            ]);

            return;
        }

        $extra = $parts[1] ?? '';

        $url = $this->services[$service] . '/' . $service;

        if ($extra !== '') {
            $url .= '/' . $extra;
        }

        $this->proxy($url);
    }

    private function proxy(string $url): void
    {

        $method = $_SERVER['REQUEST_METHOD'];

        $body = file_get_contents('php://input');

        $ch = curl_init($url);

        curl_setopt_array($ch, [

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CUSTOMREQUEST => $method,

            CURLOPT_POSTFIELDS => $body,

            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        http_response_code($status);

        header('Content-Type: application/json');

        echo $response;
    }

    private function respond(int $status, array $data): void
    {

        http_response_code($status);

        header('Content-Type: application/json');

        echo json_encode($data);
    }
}
