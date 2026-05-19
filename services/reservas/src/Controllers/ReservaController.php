<?php
namespace Reservas\Controllers;

use Reservas\Models\ReservaModel;
use Reservas\Views\ReservaView;

class ReservaController {

    private ReservaModel $model;
    private ReservaView  $view;

    public function __construct() {
        $this->model = new ReservaModel();
        $this->view  = new ReservaView();
    }

    public function index(): void {
        $this->view->json($this->model->findAll());
    }

    public function show(int $id): void {
        $reserva = $this->model->findById($id);
        $reserva
            ? $this->view->json($reserva)
            : $this->view->error('Reserva no encontrada', 404);
    }

    public function porCliente(int $clienteId): void {
        $this->view->json($this->model->findByCliente($clienteId));
    }

    public function porVehiculo(int $vehiculoId): void {
        $this->view->json($this->model->findByVehiculo($vehiculoId));
    }

    public function store(): void {
        $data = $this->body();

        $requeridos = ['vehiculo_id', 'cliente_id', 'fecha_inicio', 'fecha_fin'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                $this->view->error("El campo '{$campo}' es obligatorio", 422);
                return;
            }
        }

        if ($data['fecha_fin'] <= $data['fecha_inicio']) {
            $this->view->error('La fecha fin debe ser posterior a la fecha inicio', 422);
            return;
        }

        $this->view->json($this->model->create($data), 201);
    }

    public function update(int $id): void {
        $data    = $this->body();
        $reserva = $this->model->update($id, $data);
        $reserva
            ? $this->view->json($reserva)
            : $this->view->error('Reserva no encontrada', 404);
    }

    public function destroy(int $id): void {
        $this->model->delete($id);
        $this->view->json(['message' => 'Reserva eliminada']);
    }

    private function body(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
