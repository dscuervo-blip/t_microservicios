<?php
namespace Vehiculos\Controllers;

use Vehiculos\Models\VehiculoModel;
use Vehiculos\Views\VehiculoView;

class VehiculoController {

    private VehiculoModel $model;
    private VehiculoView  $view;

    public function __construct() {
        $this->model = new VehiculoModel();
        $this->view  = new VehiculoView();
    }

    public function index(): void {
        $this->view->json($this->model->findAll());
    }

    public function show(int $id): void {
        $vehiculo = $this->model->findById($id);
        $vehiculo
            ? $this->view->json($vehiculo)
            : $this->view->error('Vehículo no encontrado', 404);
    }

    public function disponibles(): void {
        $this->view->json($this->model->findDisponibles());
    }

    public function store(): void {
        $data = $this->body();

        $requeridos = ['marca', 'modelo', 'anio', 'categoria', 'placa'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                $this->view->error("El campo '{$campo}' es obligatorio", 422);
                return;
            }
        }

        $this->view->json($this->model->create($data), 201);
    }

    public function update(int $id): void {
        $data = $this->body();
        $vehiculo = $this->model->update($id, $data);
        $vehiculo
            ? $this->view->json($vehiculo)
            : $this->view->error('Vehículo no encontrado', 404);
    }

    public function destroy(int $id): void {
        $this->model->delete($id);
        $this->view->json(['message' => 'Vehículo eliminado']);
    }

    private function body(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
