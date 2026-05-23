<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Reservation;
use PDO;

class ReservationRepository
{
    public function __construct(private readonly PDO $pdo) {}

    // ─── SELECT con JOIN enriquecido ──────────────────────────────────────────

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT r.*,
                    c.nombre    AS nombre,
                    c.apellido  AS apellido,
                    c.correo    AS correo,
                    c.telefono  AS telefono,
                    v.marca     AS marca,
                    v.modelo    AS modelo,
                    v.placa     AS placa,
                    v.anio      AS anio_v,
                    v.categoria AS categoria_v
             FROM reservas r
             LEFT JOIN clientes  c ON r.cliente_id  = c.id
             LEFT JOIN vehiculos v ON r.vehiculo_id = v.id
             ORDER BY r.id ASC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    c.nombre    AS nombre,
                    c.apellido  AS apellido,
                    c.correo    AS correo,
                    c.telefono  AS telefono,
                    v.marca     AS marca,
                    v.modelo    AS modelo,
                    v.placa     AS placa,
                    v.anio      AS anio_v,
                    v.categoria AS categoria_v
             FROM reservas r
             LEFT JOIN clientes  c ON r.cliente_id  = c.id
             LEFT JOIN vehiculos v ON r.vehiculo_id = v.id
             WHERE r.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCliente(int $clienteId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, c.nombre AS nombre, c.apellido AS apellido,
                    v.marca AS marca, v.modelo AS modelo, v.placa AS placa
             FROM reservas r
             LEFT JOIN clientes  c ON r.cliente_id  = c.id
             LEFT JOIN vehiculos v ON r.vehiculo_id = v.id
             WHERE r.cliente_id = :cliente_id
             ORDER BY r.fecha_inicio DESC'
        );
        $stmt->execute([':cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function findByVehiculo(int $vehiculoId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, c.nombre AS nombre, c.apellido AS apellido,
                    v.marca AS marca, v.modelo AS modelo, v.placa AS placa
             FROM reservas r
             LEFT JOIN clientes  c ON r.cliente_id  = c.id
             LEFT JOIN vehiculos v ON r.vehiculo_id = v.id
             WHERE r.vehiculo_id = :vehiculo_id
             ORDER BY r.fecha_inicio DESC'
        );
        $stmt->execute([':vehiculo_id' => $vehiculoId]);
        return $stmt->fetchAll();
    }

    public function findByEstado(string $estado): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, c.nombre AS nombre, c.apellido AS apellido,
                    v.marca AS marca, v.modelo AS modelo, v.placa AS placa
             FROM reservas r
             LEFT JOIN clientes  c ON r.cliente_id  = c.id
             LEFT JOIN vehiculos v ON r.vehiculo_id = v.id
             WHERE r.estado = :estado
             ORDER BY r.fecha_inicio ASC'
        );
        $stmt->execute([':estado' => $estado]);
        return $stmt->fetchAll();
    }

    // ─── Validaciones ─────────────────────────────────────────────────────────

    public function hasOverlap(int $vehiculoId, string $fechaInicio, string $fechaFin, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM reservas
                WHERE vehiculo_id = :vehiculo_id
                  AND estado = 'activa'
                  AND fecha_inicio < :fecha_fin
                  AND fecha_fin    > :fecha_inicio";
        $params = [
            ':vehiculo_id'  => $vehiculoId,
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin'    => $fechaFin,
        ];
        if ($excludeId !== null) {
            $sql                   .= ' AND id != :exclude_id';
            $params[':exclude_id']  = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function clienteExists(int $clienteId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $clienteId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function vehiculoExists(int $vehiculoId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM vehiculos WHERE id = :id');
        $stmt->execute([':id' => $vehiculoId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getVehicleEstado(int $vehiculoId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT estado FROM vehiculos WHERE id = :id');
        $stmt->execute([':id' => $vehiculoId]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (string) $result : null;
    }

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    public function create(Reservation $reservation): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reservas (cliente_id, vehiculo_id, fecha_inicio, fecha_fin, estado, valor_total)
             VALUES (:cliente_id, :vehiculo_id, :fecha_inicio, :fecha_fin, :estado, :valor_total)'
        );
        $stmt->execute([
            ':cliente_id'   => $reservation->cliente_id,
            ':vehiculo_id'  => $reservation->vehiculo_id,
            ':fecha_inicio' => $reservation->fecha_inicio,
            ':fecha_fin'    => $reservation->fecha_fin,
            ':estado'       => $reservation->estado,
            ':valor_total'  => $reservation->valor_total,
        ]);
        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): ?array
    {
        $allowed    = ['cliente_id', 'vehiculo_id', 'fecha_inicio', 'fecha_fin', 'estado', 'valor_total'];
        $setClauses = [];
        $params     = [':id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $setClauses[]      = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($setClauses)) {
            return $this->findById($id);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE reservas SET ' . implode(', ', $setClauses) . ' WHERE id = :id'
        );
        $stmt->execute($params);
        return $this->findById($id);
    }

    public function updateEstado(int $id, string $estado): ?array
    {
        $stmt = $this->pdo->prepare('UPDATE reservas SET estado = :estado WHERE id = :id');
        $stmt->execute([':estado' => $estado, ':id' => $id]);
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM reservas WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateVehicleEstado(int $vehiculoId, string $estado): void
    {
        $stmt = $this->pdo->prepare('UPDATE vehiculos SET estado = :estado WHERE id = :id');
        $stmt->execute([':estado' => $estado, ':id' => $vehiculoId]);
    }
}
