<?php
namespace Devoluciones\Controllers;

use Devoluciones\Models\DevolucionModel;
use Devoluciones\Views\DevolucionView;

class DevolucionController {

    private DevolucionModel $model;
    private DevolucionView  $view;

    public function __construct() {
        $this->model = new DevolucionModel();
        $this->view  = new DevolucionView();
    }

    public function index(): void {
        $this->view->json($this->model->findAll());
    }

    public function show(int $id): void {
        $devolucion = $this->model->findById($id);
        $devolucion
            ? $this->view->json($devolucion)
            : $this->view->error('Devolución no encontrada', 404);
    }

    public function porReserva(int $reservaId): void {
        $devolucion = $this->model->findByReserva($reservaId);
        $this->view->json($devolucion ?: null);
    }

    public function store(): void {
        $data = $this->body();

        $requeridos = ['reserva_id', 'vehiculo_id', 'cliente_id', 'fecha_devolucion'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                $this->view->error("El campo '{$campo}' es obligatorio", 422);
                return;
            }
        }

        $this->view->json($this->model->create($data), 201);
    }

    public function destroy(int $id): void {
        $this->model->delete($id);
        $this->view->json(['message' => 'Devolución eliminada']);
    }

    private function body(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
