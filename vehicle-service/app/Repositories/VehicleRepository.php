<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Vehicle;
use PDO;

class VehicleRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM vehiculos ORDER BY id ASC');

        return array_map(
            fn(array $row) => Vehicle::fromArray($row),
            $stmt->fetchAll()
        );
    }

    public function findById(int $id): ?Vehicle
    {
        $stmt = $this->pdo->prepare('SELECT * FROM vehiculos WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row ? Vehicle::fromArray($row) : null;
    }

    public function findAvailable(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vehiculos WHERE estado = 'disponible' ORDER BY id ASC"
        );
        $stmt->execute();

        return array_map(
            fn(array $row) => Vehicle::fromArray($row),
            $stmt->fetchAll()
        );
    }

    public function findByCategory(string $category): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM vehiculos WHERE categoria = :categoria ORDER BY id ASC'
        );
        $stmt->execute([':categoria' => $category]);

        return array_map(
            fn(array $row) => Vehicle::fromArray($row),
            $stmt->fetchAll()
        );
    }

    public function create(Vehicle $vehicle): Vehicle
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO vehiculos (marca, modelo, anio, categoria, estado)
             VALUES (:marca, :modelo, :anio, :categoria, :estado)'
        );

        $stmt->execute([
            ':marca'     => $vehicle->marca,
            ':modelo'    => $vehicle->modelo,
            ':anio'      => $vehicle->anio,
            ':categoria' => $vehicle->categoria,
            ':estado'    => $vehicle->estado,
        ]);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): ?Vehicle
    {
        $allowedFields = ['marca', 'modelo', 'anio', 'categoria', 'estado'];
        $setClauses    = [];
        $params        = [':id' => $id];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $setClauses[]      = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($setClauses)) {
            return $this->findById($id);
        }

        $sql  = 'UPDATE vehiculos SET ' . implode(', ', $setClauses) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM vehiculos WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
