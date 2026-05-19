<?php
namespace Gateway;

class Gateway {

    private const BASE = '/t_microservicios/gateway/public';

    private array $services = [
        'vehiculos'    => 'http://localhost/t_microservicios/services/vehiculos/public',
        'clientes'     => 'http://localhost/t_microservicios/services/clientes/public',
        'reservas'     => 'http://localhost/t_microservicios/services/reservas/public',
        'devoluciones' => 'http://localhost/t_microservicios/services/devoluciones/public',
    ];

    public function handle(): void {
        $uri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path    = ltrim(str_replace(self::BASE, '', $uri), '/');
        $parts   = explode('/', $path, 2);
        $service = $parts[0] ?? '';

        if (!array_key_exists($service, $this->services)) {
            $this->respond(404, ['error' => "Servicio '{$service}' no encontrado"]);
            return;
        }

        $targetPath = '/' . ($parts[1] ?? '');
        $query      = $_SERVER['QUERY_STRING'] ?? '';
        $url        = $this->services[$service] . $targetPath . ($query ? "?{$query}" : '');

        $this->proxy($url);
    }

    private function proxy(string $url): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $body   = file_get_contents('php://input');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            $this->respond(502, ['error' => 'Servicio no disponible', 'detalle' => $error]);
            return;
        }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo $response;
    }

    private function respond(int $status, array $data): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
