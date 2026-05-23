<?php
namespace Clientes\Controllers;

use Clientes\Models\ClienteModel;
use Clientes\Views\ClienteView;

class ClienteController {

    private ClienteModel $model;
    private ClienteView  $view;

    public function __construct() {
        $this->model = new ClienteModel();
        $this->view  = new ClienteView();
    }

    public function index(): void {
        $this->view->json($this->model->findAll());
    }

    public function show(int $id): void {
        $cliente = $this->model->findById($id);
        $cliente
            ? $this->view->json($cliente)
            : $this->view->error('Cliente no encontrado', 404);
    }

    public function store(): void {
        $data = $this->body();

        $requeridos = ['nombre', 'apellido', 'email', 'documento', 'numero_licencia'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                $this->view->error("El campo '{$campo}' es obligatorio", 422);
                return;
            }
        }

        $this->view->json($this->model->create($data), 201);
    }

    public function update(int $id): void {
        $data    = $this->body();
        $cliente = $this->model->update($id, $data);
        $cliente
            ? $this->view->json($cliente)
            : $this->view->error('Cliente no encontrado', 404);
    }

    public function destroy(int $id): void {
        $this->model->delete($id);
        $this->view->json(['message' => 'Cliente eliminado']);
    }

    private function body(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
