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
        $vehicles = $this->repository->findAll();

        return array_map(fn(Vehicle $v) => $v->toArray(), $vehicles);
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
        $vehicles = $this->repository->findAvailable();

        return array_map(fn(Vehicle $v) => $v->toArray(), $vehicles);
    }

    public function getVehiclesByCategory(string $category): array
    {
        if (empty(trim($category))) {
            throw new InvalidArgumentException('La categoría no puede estar vacía.');
        }

        $vehicles = $this->repository->findByCategory($category);

        return array_map(fn(Vehicle $v) => $v->toArray(), $vehicles);
    }

    public function createVehicle(array $data): array
    {
        $this->validateRequiredFields($data);
        $this->validateAnio((int) $data['anio']);
        $this->validateEstado($data['estado'] ?? 'disponible');

        $vehicle = Vehicle::fromArray($data);

        return $this->repository->create($vehicle)->toArray();
    }

    public function updateVehicle(int $id, array $data): array
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new RuntimeException("Vehículo con ID $id no encontrado.", 404);
        }

        if (isset($data['anio'])) {
            $this->validateAnio((int) $data['anio']);
        }

        if (isset($data['estado'])) {
            $this->validateEstado($data['estado']);
        }

        $updated = $this->repository->update($id, $data);

        return $updated->toArray();
    }

    public function deleteVehicle(int $id): void
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new RuntimeException("Vehículo con ID $id no encontrado.", 404);
        }

        $this->repository->delete($id);
    }

    private function validateRequiredFields(array $data): void
    {
        $required = ['marca', 'modelo', 'anio', 'categoria'];

        foreach ($required as $field) {
            if (empty($data[$field]) && $data[$field] !== 0) {
                throw new InvalidArgumentException("El campo '$field' es obligatorio.");
            }
        }
    }

    private function validateAnio(int $anio): void
    {
        $currentYear = (int) date('Y');

        if ($anio < 1900 || $anio > $currentYear + 1) {
            throw new InvalidArgumentException(
                "El año debe estar entre 1900 y " . ($currentYear + 1) . "."
            );
        }
    }

    private function validateEstado(string $estado): void
    {
        if (!in_array($estado, self::VALID_STATES, true)) {
            throw new InvalidArgumentException(
                "Estado inválido. Valores permitidos: " . implode(', ', self::VALID_STATES) . "."
            );
        }
    }
}
