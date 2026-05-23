<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reservation;
use App\Repositories\ReservationRepository;
use InvalidArgumentException;
use RuntimeException;

class ReservationService
{
    // Estados reales según ENUM de la BD (no existe 'pendiente')
    private const VALID_STATES = ['activa', 'completada', 'cancelada'];

    // Transiciones permitidas
    private const STATE_TRANSITIONS = [
        'activa'     => ['completada', 'cancelada'],
        'completada' => [],
        'cancelada'  => [],
    ];

    public function __construct(private readonly ReservationRepository $repository) {}

    // ─── Consultas ────────────────────────────────────────────────────────────

    public function getAllReservations(): array
    {
        return $this->repository->findAll();
    }

    public function getReservationById(int $id): array
    {
        $reservation = $this->repository->findById($id);

        if ($reservation === null) {
            throw new RuntimeException("Reserva con ID $id no encontrada.", 404);
        }

        return $reservation;
    }

    public function getReservationsByCliente(int $clienteId): array
    {
        if (!$this->repository->clienteExists($clienteId)) {
            throw new RuntimeException("Cliente con ID $clienteId no encontrado.", 404);
        }

        return $this->repository->findByCliente($clienteId);
    }

    public function getReservationsByVehiculo(int $vehiculoId): array
    {
        if (!$this->repository->vehiculoExists($vehiculoId)) {
            throw new RuntimeException("Vehículo con ID $vehiculoId no encontrado.", 404);
        }

        return $this->repository->findByVehiculo($vehiculoId);
    }

    public function getReservationsByEstado(string $estado): array
    {
        if (!in_array($estado, self::VALID_STATES, true)) {
            throw new InvalidArgumentException(
                "Estado inválido. Valores permitidos: " . implode(', ', self::VALID_STATES) . "."
            );
        }

        return $this->repository->findByEstado($estado);
    }

    // ─── Creación ─────────────────────────────────────────────────────────────

    public function createReservation(array $data): array
    {
        $this->validateRequiredFields($data);
        $this->validateDates($data['fecha_inicio'], $data['fecha_fin']);

        $clienteId  = (int) $data['cliente_id'];
        $vehiculoId = (int) $data['vehiculo_id'];

        if (!$this->repository->clienteExists($clienteId)) {
            throw new InvalidArgumentException("El cliente con ID $clienteId no existe.");
        }

        if (!$this->repository->vehiculoExists($vehiculoId)) {
            throw new InvalidArgumentException("El vehículo con ID $vehiculoId no existe.");
        }

        $vehicleEstado = $this->repository->getVehicleEstado($vehiculoId);

        if ($vehicleEstado !== 'disponible') {
            throw new InvalidArgumentException(
                "El vehículo con ID $vehiculoId no está disponible (estado actual: $vehicleEstado)."
            );
        }

        if ($this->repository->hasOverlap($vehiculoId, $data['fecha_inicio'], $data['fecha_fin'])) {
            throw new InvalidArgumentException(
                "El vehículo ya tiene una reserva activa en ese rango de fechas."
            );
        }

        // La reserva inicia como 'activa' y el vehículo pasa a 'alquilado'
        $data['estado'] = 'activa';
        $reservation    = Reservation::fromArray($data);
        $created        = $this->repository->create($reservation);

        $this->repository->updateVehicleEstado($vehiculoId, 'alquilado');

        return $created;
    }

    // ─── Actualización ────────────────────────────────────────────────────────

    public function updateReservation(int $id, array $data): array
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new RuntimeException("Reserva con ID $id no encontrada.", 404);
        }

        if (in_array($existing['estado'], ['completada', 'cancelada'], true)) {
            throw new InvalidArgumentException(
                "No se puede modificar una reserva con estado '{$existing['estado']}'."
            );
        }

        if (isset($data['estado'])) {
            $this->validateTransition($existing['estado'], $data['estado']);
        }

        if (isset($data['fecha_inicio']) || isset($data['fecha_fin'])) {
            $fechaInicio = $data['fecha_inicio'] ?? $existing['fecha_inicio'];
            $fechaFin    = $data['fecha_fin']    ?? $existing['fecha_fin'];

            $this->validateDates($fechaInicio, $fechaFin);

            $vehiculoId = (int) ($data['vehiculo_id'] ?? $existing['vehiculo_id']);

            if ($this->repository->hasOverlap($vehiculoId, $fechaInicio, $fechaFin, $id)) {
                throw new InvalidArgumentException(
                    "El vehículo ya tiene otra reserva activa en ese rango de fechas."
                );
            }
        }

        return $this->repository->update($id, $data);
    }

    // ─── Transiciones de estado ───────────────────────────────────────────────

    public function cancelReservation(int $id): array
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new RuntimeException("Reserva con ID $id no encontrada.", 404);
        }

        $this->validateTransition($existing['estado'], 'cancelada');

        // Liberar el vehículo
        $this->repository->updateVehicleEstado((int) $existing['vehiculo_id'], 'disponible');

        return $this->repository->updateEstado($id, 'cancelada');
    }

    public function completeReservation(int $id): array
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new RuntimeException("Reserva con ID $id no encontrada.", 404);
        }

        $this->validateTransition($existing['estado'], 'completada');

        // Liberar el vehículo
        $this->repository->updateVehicleEstado((int) $existing['vehiculo_id'], 'disponible');

        return $this->repository->updateEstado($id, 'completada');
    }

    // ─── Eliminación ─────────────────────────────────────────────────────────

    public function deleteReservation(int $id): void
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new RuntimeException("Reserva con ID $id no encontrada.", 404);
        }

        if ($existing['estado'] === 'activa') {
            throw new InvalidArgumentException(
                "No se puede eliminar una reserva activa. Cancélela primero."
            );
        }

        $this->repository->delete($id);
    }

    // ─── Validaciones privadas ────────────────────────────────────────────────

    private function validateRequiredFields(array $data): void
    {
        $required = ['cliente_id', 'vehiculo_id', 'fecha_inicio', 'fecha_fin'];

        foreach ($required as $field) {
            if (empty($data[$field]) && $data[$field] !== 0) {
                throw new InvalidArgumentException("El campo '$field' es obligatorio.");
            }
        }
    }

    private function validateDates(string $fechaInicio, string $fechaFin): void
    {
        $inicio = \DateTime::createFromFormat('Y-m-d', $fechaInicio);
        $fin    = \DateTime::createFromFormat('Y-m-d', $fechaFin);

        if (!$inicio || $inicio->format('Y-m-d') !== $fechaInicio) {
            throw new InvalidArgumentException(
                "La fecha de inicio '$fechaInicio' no tiene formato válido (YYYY-MM-DD)."
            );
        }

        if (!$fin || $fin->format('Y-m-d') !== $fechaFin) {
            throw new InvalidArgumentException(
                "La fecha de fin '$fechaFin' no tiene formato válido (YYYY-MM-DD)."
            );
        }

        if ($inicio >= $fin) {
            throw new InvalidArgumentException(
                "La fecha de inicio debe ser anterior a la fecha de fin."
            );
        }

        $today = new \DateTime('today');

        if ($inicio < $today) {
            throw new InvalidArgumentException(
                "La fecha de inicio no puede ser anterior a hoy."
            );
        }
    }

    private function validateTransition(string $currentState, string $newState): void
    {
        $allowed = self::STATE_TRANSITIONS[$currentState] ?? [];

        if (!in_array($newState, $allowed, true)) {
            $allowedStr = empty($allowed)
                ? 'ninguno (estado final)'
                : implode(', ', $allowed);

            throw new InvalidArgumentException(
                "No se puede cambiar el estado de '$currentState' a '$newState'. "
                . "Transiciones permitidas: $allowedStr."
            );
        }
    }
}
