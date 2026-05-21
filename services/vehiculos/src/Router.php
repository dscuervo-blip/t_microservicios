<?php
namespace Vehiculos;

class Router {

    private array $routes = [];

    public function addRoute(string $method, string $path, array $handler): void {

        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void {

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = $this->getUri();

        foreach ($this->routes as $route) {

            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{(\w+)\}/', '(\d+)', $route['path']);

            if (preg_match("#^{$pattern}$#", $uri, $matches)) {

                array_shift($matches);

                [$class, $action] = $route['handler'];

                $controller = new $class();

                call_user_func_array(
                    [$controller, $action],
                    array_map('intval', $matches)
                );

                return;
            }
        }

        http_response_code(404);

        header('Content-Type: application/json');

        echo json_encode([
            'error' => 'Ruta no encontrada',
            'uri'   => $uri
        ]);
    }

    private function getUri(): string {

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $uri = str_replace(
            '/t_microservicios/services/vehiculos/public/index.php',
            '',
            $uri
        );

        return $uri ?: '/';
    }
}