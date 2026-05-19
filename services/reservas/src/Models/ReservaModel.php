<?php
namespace Reservas\Models;

use PDO;

class ReservaModel {

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
            'SELECT r.*,
                    v.marca, v.modelo, v.placa,
                    c.nombre, c.apellido
             FROM reservas r
             JOIN vehiculos v ON r.vehiculo_id = v.id
             JOIN clientes  c ON r.cliente_id  = c.id
             ORDER BY r.id DESC'
        )->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(
            'SELECT r.*,
                    v.marca, v.modelo, v.placa,
                    c.nombre, c.apellido
             FROM reservas r
             JOIN vehiculos v ON r.vehiculo_id = v.id
             JOIN clientes  c ON r.cliente_id  = c.id
             WHERE r.id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByCliente(int $clienteId): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, v.marca, v.modelo, v.placa
             FROM reservas r
             JOIN vehiculos v ON r.vehiculo_id = v.id
             WHERE r.cliente_id = :id ORDER BY r.id DESC'
        );
        $stmt->execute([':id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function findByVehiculo(int $vehiculoId): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, c.nombre, c.apellido
             FROM reservas r
             JOIN clientes c ON r.cliente_id = c.id
             WHERE r.vehiculo_id = :id ORDER BY r.id DESC'
        );
        $stmt->execute([':id' => $vehiculoId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): array|false {
        $stmt = $this->db->prepare(
            'INSERT INTO reservas (vehiculo_id, cliente_id, fecha_inicio, fecha_fin, estado, valor_total)
             VALUES (:vehiculo_id, :cliente_id, :fecha_inicio, :fecha_fin, :estado, :valor_total)'
        );
        $stmt->execute([
            ':vehiculo_id'  => $data['vehiculo_id'],
            ':cliente_id'   => $data['cliente_id'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin'    => $data['fecha_fin'],
            ':estado'       => $data['estado']      ?? 'pendiente',
            ':valor_total'  => $data['valor_total'] ?? 0,
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): array|false {
        $stmt = $this->db->prepare(
            'UPDATE reservas
             SET vehiculo_id=:vehiculo_id, cliente_id=:cliente_id,
                 fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin,
                 estado=:estado, valor_total=:valor_total
             WHERE id = :id'
        );
        $stmt->execute([
            ':vehiculo_id'  => $data['vehiculo_id'],
            ':cliente_id'   => $data['cliente_id'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin'    => $data['fecha_fin'],
            ':estado'       => $data['estado'],
            ':valor_total'  => $data['valor_total'],
            ':id'           => $id,
        ]);
        return $this->findById($id);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM reservas WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
