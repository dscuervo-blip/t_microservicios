<?php
namespace Vehiculos\Models;

use PDO;

class VehiculoModel {

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
        return $this->db
            ->query('SELECT * FROM vehiculos ORDER BY id DESC')
            ->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM vehiculos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findDisponibles(): array {
        $stmt = $this->db->prepare("SELECT * FROM vehiculos WHERE estado = 'disponible' ORDER BY marca");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): array|false {
        $stmt = $this->db->prepare(
            'INSERT INTO vehiculos (marca, modelo, anio, categoria, placa, estado)
             VALUES (:marca, :modelo, :anio, :categoria, :placa, :estado)'
        );
        $stmt->execute([
            ':marca'     => $data['marca'],
            ':modelo'    => $data['modelo'],
            ':anio'      => $data['anio'],
            ':categoria' => $data['categoria'],
            ':placa'     => strtoupper($data['placa']),
            ':estado'    => $data['estado'] ?? 'disponible',
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): array|false {
        $stmt = $this->db->prepare(
            'UPDATE vehiculos
             SET marca=:marca, modelo=:modelo, anio=:anio,
                 categoria=:categoria, placa=:placa, estado=:estado
             WHERE id = :id'
        );
        $stmt->execute([
            ':marca'     => $data['marca'],
            ':modelo'    => $data['modelo'],
            ':anio'      => $data['anio'],
            ':categoria' => $data['categoria'],
            ':placa'     => strtoupper($data['placa']),
            ':estado'    => $data['estado'],
            ':id'        => $id,
        ]);
        return $this->findById($id);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM vehiculos WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
