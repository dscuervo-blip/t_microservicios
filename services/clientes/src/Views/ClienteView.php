<?php
namespace Clientes\Views;

class ClienteView {

    public function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function error(string $message, int $status = 400): void {
        $this->json(['error' => $message], $status);
    }
}
