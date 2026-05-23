<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Customer;
use PDO;

class CustomerRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM clientes ORDER BY apellido ASC, nombre ASC');
        return array_map(fn(array $row) => Customer::fromArray($row), $stmt->fetchAll());
    }

    public function findById(int $id): ?Customer
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Customer::fromArray($row) : null;
    }

    public function search(string $query): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM clientes
             WHERE nombre LIKE :q OR apellido LIKE :q OR correo LIKE :q OR telefono LIKE :q OR documento LIKE :q
             ORDER BY apellido ASC, nombre ASC'
        );
        $stmt->execute([':q' => "%$query%"]);
        return array_map(fn(array $row) => Customer::fromArray($row), $stmt->fetchAll());
    }

    public function create(Customer $customer): Customer
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO clientes (nombre, apellido, correo, telefono, documento, numero_licencia)
             VALUES (:nombre, :apellido, :correo, :telefono, :documento, :numero_licencia)'
        );
        $stmt->execute([
            ':nombre'          => $customer->nombre,
            ':apellido'        => $customer->apellido,
            ':correo'          => $customer->email,
            ':telefono'        => $customer->telefono,
            ':documento'       => $customer->documento,
            ':numero_licencia' => $customer->numero_licencia,
        ]);
        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): ?Customer
    {
        // mapea 'email' → 'correo' para la BD
        if (isset($data['email'])) {
            $data['correo'] = $data['email'];
            unset($data['email']);
        }

        $allowed    = ['nombre', 'apellido', 'correo', 'telefono', 'documento', 'numero_licencia'];
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
            'UPDATE clientes SET ' . implode(', ', $setClauses) . ' WHERE id = :id'
        );
        $stmt->execute($params);
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM clientes WHERE correo = :correo';
        $params = [':correo' => $email];
        if ($excludeId !== null) {
            $sql          .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function licenciaExists(string $licencia, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM clientes WHERE numero_licencia = :numero_licencia';
        $params = [':numero_licencia' => $licencia];
        if ($excludeId !== null) {
            $sql          .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
