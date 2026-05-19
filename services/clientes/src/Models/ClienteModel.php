<?php
namespace Clientes\Models;

use PDO;

class ClienteModel {

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
            ->query('SELECT * FROM clientes ORDER BY apellido, nombre')
            ->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): array|false {
        $stmt = $this->db->prepare(
            'INSERT INTO clientes (nombre, apellido, email, telefono, documento, numero_licencia)
             VALUES (:nombre, :apellido, :email, :telefono, :documento, :numero_licencia)'
        );
        $stmt->execute([
            ':nombre'          => $data['nombre'],
            ':apellido'        => $data['apellido'],
            ':email'           => $data['email'],
            ':telefono'        => $data['telefono']        ?? null,
            ':documento'       => $data['documento'],
            ':numero_licencia' => $data['numero_licencia'],
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): array|false {
        $stmt = $this->db->prepare(
            'UPDATE clientes
             SET nombre=:nombre, apellido=:apellido, email=:email,
                 telefono=:telefono, documento=:documento, numero_licencia=:numero_licencia
             WHERE id = :id'
        );
        $stmt->execute([
            ':nombre'          => $data['nombre'],
            ':apellido'        => $data['apellido'],
            ':email'           => $data['email'],
            ':telefono'        => $data['telefono']        ?? null,
            ':documento'       => $data['documento'],
            ':numero_licencia' => $data['numero_licencia'],
            ':id'              => $id,
        ]);
        return $this->findById($id);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
