<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DevolucionRepository;
use InvalidArgumentException;
use RuntimeException;

class DevolucionService
{
    public function __construct(private readonly DevolucionRepository $repository) {}

    public function getAllDevoluciones(): array
    {
        return $this->repository->findAll();
    }

    public function getDevolucionById(int $id): array
    {
        $dev = $this->repository->findById($id);
        if ($dev === null) {
            throw new RuntimeException("Devolución con ID $id no encontrada.", 404);
        }
        return $dev;
    }

    public function createDevolucion(array $data): array
    {
        $required = ['reserva_id', 'vehiculo_id', 'cliente_id', 'fecha_devolucion'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("El campo '$field' es obligatorio.");
            }
        }

        if (!$this->repository->reservaExists((int) $data['reserva_id'])) {
            throw new InvalidArgumentException("La reserva con ID {$data['reserva_id']} no existe.");
        }

        $reserva = $this->repository->getReserva((int) $data['reserva_id']);
        if ($reserva && $reserva['estado'] === 'cancelada') {
            throw new InvalidArgumentException("No se puede registrar devolución de una reserva cancelada.");
        }

        $devolucion = $this->repository->create($data);

        // Marcar reserva como completada y vehículo como disponible
        $this->repository->updateReservaEstado((int) $data['reserva_id'], 'completada');
        $this->repository->updateVehicleEstado((int) $data['vehiculo_id'], 'disponible');

        return $devolucion;
    }

    public function deleteDevolucion(int $id): void
    {
        if ($this->repository->findById($id) === null) {
            throw new RuntimeException("Devolución con ID $id no encontrada.", 404);
        }
        $this->repository->delete($id);
    }
}
