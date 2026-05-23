<?php
namespace Devoluciones\Models;

use PDO;

class DevolucionModel {

    private PDO $db;

    public function __construct() {
        $cfg      = require __DIR__ . '/../../config/database.php';
        $dsn      = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
        $this->db = new PDO($dsn, $cfg['user'], $cfg['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function findAll(): array {
        return $this->db->query(
            'SELECT d.*,
                    v.marca, v.modelo, v.placa,
                    c.nombre, c.apellido
             FROM devoluciones d
             JOIN vehiculos v ON d.vehiculo_id = v.id
             JOIN clientes  c ON d.cliente_id  = c.id
             ORDER BY d.id DESC'
        )->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(
            'SELECT d.*,
                    v.marca, v.modelo, v.placa,
                    c.nombre, c.apellido
             FROM devoluciones d
             JOIN vehiculos v ON d.vehiculo_id = v.id
             JOIN clientes  c ON d.cliente_id  = c.id
             WHERE d.id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByReserva(int $reservaId): array|false {
        $stmt = $this->db->prepare('SELECT * FROM devoluciones WHERE reserva_id = :id');
        $stmt->execute([':id' => $reservaId]);
        return $stmt->fetch();
    }

    public function create(array $data): array|false {
        $stmt = $this->db->prepare(
            'INSERT INTO devoluciones
                (reserva_id, vehiculo_id, cliente_id, fecha_devolucion, km_recorridos, estado_vehiculo, observaciones)
             VALUES
                (:reserva_id, :vehiculo_id, :cliente_id, :fecha_devolucion, :km_recorridos, :estado_vehiculo, :observaciones)'
        );
        $stmt->execute([
            ':reserva_id'       => $data['reserva_id'],
            ':vehiculo_id'      => $data['vehiculo_id'],
            ':cliente_id'       => $data['cliente_id'],
            ':fecha_devolucion' => $data['fecha_devolucion'],
            ':km_recorridos'    => $data['km_recorridos']   ?? 0,
            ':estado_vehiculo'  => $data['estado_vehiculo'] ?? 'bueno',
            ':observaciones'    => $data['observaciones']   ?? null,
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM devoluciones WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
