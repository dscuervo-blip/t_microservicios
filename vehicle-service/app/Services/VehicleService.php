<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use InvalidArgumentException;
use RuntimeException;

class VehicleService
{
    private const VALID_STATES = ['disponible', 'alquilado', 'mantenimiento'];

    public function __construct(private readonly VehicleRepository $repository) {}

    public function getAllVehicles(): array
    {
        return array_map(fn(Vehicle $v) => $v->toArray(), $this->repository->findAll());
    }

    public function getVehicleById(int $id): array
    {
        $vehicle = $this->repository->findById($id);
        if ($vehicle === null) {
            throw new RuntimeException("Vehículo con ID $id no encontrado.", 404);
        }
        return $vehicle->toArray();
    }

    public function getAvailableVehicles(): array
    {
        return array_map(fn(Vehicle $v) => $v->toArray(), $this->repository->findAvailable());
    }

    public function getVehiclesByCategory(string $category): array
    {
        if (empty(trim($category))) {
            throw new InvalidArgumentException('La categoría no puede estar vacía.');
        }
        return array_map(fn(Vehicle $v) => $v->toArray(), $this->repository->findByCategory($category));
    }

    public function createVehicle(array $data): array
    {
        $this->validateRequired($data);
        $this->validateAnio((int) $data['anio']);
        $this->validateEstado($data['estado'] ?? 'disponible');

        if (!empty($data['placa']) && $this->repository->placaExists($data['placa'])) {
            throw new InvalidArgumentException("La placa '{$data['placa']}' ya está registrada.");
        }

        return $this->repository->create(Vehicle::fromArray($data))->toArray();
    }

    public function updateVehicle(int $id, array $data): array
    {
        if ($this->repository->findById($id) === null) {
            throw new RuntimeException("Vehículo con ID $id no encontrado.", 404);
        }
        if (isset($data['anio'])) {
            $this->validateAnio((int) $data['anio']);
        }
        if (isset($data['estado'])) {
            $this->validateEstado($data['estado']);
        }
        if (!empty($data['placa']) && $this->repository->placaExists($data['placa'], $id)) {
            throw new InvalidArgumentException("La placa '{$data['placa']}' ya está registrada.");
        }
        return $this->repository->update($id, $data)->toArray();
    }

    public function deleteVehicle(int $id): void
    {
        if ($this->repository->findById($id) === null) {
            throw new RuntimeException("Vehículo con ID $id no encontrado.", 404);
        }
        $this->repository->delete($id);
    }

    private function validateRequired(array $data): void
    {
        foreach (['marca', 'modelo', 'anio'] as $field) {
            if (empty($data[$field]) && ($data[$field] ?? '') !== '0') {
                throw new InvalidArgumentException("El campo '$field' es obligatorio.");
            }
        }
    }

    private function validateAnio(int $anio): void
    {
        $max = (int) date('Y') + 1;
        if ($anio < 1900 || $anio > $max) {
            throw new InvalidArgumentException("El año debe estar entre 1900 y $max.");
        }
    }

    private function validateEstado(string $estado): void
    {
        if (!in_array($estado, self::VALID_STATES, true)) {
            throw new InvalidArgumentException(
                'Estado inválido. Valores permitidos: ' . implode(', ', self::VALID_STATES) . '.'
            );
        }
    }
}
