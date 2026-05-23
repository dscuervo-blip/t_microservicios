<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class DevolucionRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT d.*,
                    v.marca     AS marca,
                    v.modelo    AS modelo,
                    v.placa     AS placa,
                    c.nombre    AS nombre,
                    c.apellido  AS apellido
             FROM devoluciones d
             LEFT JOIN vehiculos v ON d.vehiculo_id = v.id
             LEFT JOIN clientes  c ON d.cliente_id  = c.id
             ORDER BY d.id ASC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT d.*,
                    v.marca AS marca, v.modelo AS modelo, v.placa AS placa,
                    c.nombre AS nombre, c.apellido AS apellido
             FROM devoluciones d
             LEFT JOIN vehiculos v ON d.vehiculo_id = v.id
             LEFT JOIN clientes  c ON d.cliente_id  = c.id
             WHERE d.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function reservaExists(int $reservaId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM reservas WHERE id = :id');
        $stmt->execute([':id' => $reservaId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getReserva(int $reservaId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservas WHERE id = :id');
        $stmt->execute([':id' => $reservaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO devoluciones (reserva_id, vehiculo_id, cliente_id, fecha_devolucion, km_recorridos, estado_vehiculo, observaciones)
             VALUES (:reserva_id, :vehiculo_id, :cliente_id, :fecha_devolucion, :km_recorridos, :estado_vehiculo, :observaciones)'
        );
        $stmt->execute([
            ':reserva_id'      => (int) $data['reserva_id'],
            ':vehiculo_id'     => (int) $data['vehiculo_id'],
            ':cliente_id'      => (int) $data['cliente_id'],
            ':fecha_devolucion'=> $data['fecha_devolucion'],
            ':km_recorridos'   => (int) ($data['km_recorridos'] ?? 0),
            ':estado_vehiculo' => $data['estado_vehiculo'] ?? 'bueno',
            ':observaciones'   => $data['observaciones'] ?? null,
        ]);
        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM devoluciones WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateReservaEstado(int $reservaId, string $estado): void
    {
        $stmt = $this->pdo->prepare('UPDATE reservas SET estado = :estado WHERE id = :id');
        $stmt->execute([':estado' => $estado, ':id' => $reservaId]);
    }

    public function updateVehicleEstado(int $vehiculoId, string $estado): void
    {
        $stmt = $this->pdo->prepare('UPDATE vehiculos SET estado = :estado WHERE id = :id');
        $stmt->execute([':estado' => $estado, ':id' => $vehiculoId]);
    }
}
