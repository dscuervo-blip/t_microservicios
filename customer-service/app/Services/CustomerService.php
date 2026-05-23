<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use InvalidArgumentException;
use RuntimeException;

class CustomerService
{
    public function __construct(private readonly CustomerRepository $repository) {}

    public function getAllCustomers(): array
    {
        return array_map(fn(Customer $c) => $c->toArray(), $this->repository->findAll());
    }

    public function getCustomerById(int $id): array
    {
        $customer = $this->repository->findById($id);
        if ($customer === null) {
            throw new RuntimeException("Cliente con ID $id no encontrado.", 404);
        }
        return $customer->toArray();
    }

    public function searchCustomers(string $query): array
    {
        if (empty(trim($query))) {
            throw new InvalidArgumentException('El término de búsqueda no puede estar vacío.');
        }
        return array_map(fn(Customer $c) => $c->toArray(), $this->repository->search($query));
    }

    public function createCustomer(array $data): array
    {
        if (empty($data['nombre'])) {
            throw new InvalidArgumentException("El campo 'nombre' es obligatorio.");
        }

        $email = $data['email'] ?? $data['correo'] ?? null;

        if (!empty($email)) {
            $this->validateEmail($email);
            if ($this->repository->emailExists($email)) {
                throw new InvalidArgumentException("El correo '$email' ya está registrado.");
            }
        }

        if (!empty($data['numero_licencia'])) {
            if ($this->repository->licenciaExists($data['numero_licencia'])) {
                throw new InvalidArgumentException(
                    "La licencia '{$data['numero_licencia']}' ya está registrada."
                );
            }
        }

        return $this->repository->create(Customer::fromArray($data))->toArray();
    }

    public function updateCustomer(int $id, array $data): array
    {
        if ($this->repository->findById($id) === null) {
            throw new RuntimeException("Cliente con ID $id no encontrado.", 404);
        }

        $email = $data['email'] ?? $data['correo'] ?? null;

        if (!empty($email)) {
            $this->validateEmail($email);
            if ($this->repository->emailExists($email, $id)) {
                throw new InvalidArgumentException(
                    "El correo '$email' ya está registrado por otro cliente."
                );
            }
        }

        if (!empty($data['numero_licencia'])) {
            if ($this->repository->licenciaExists($data['numero_licencia'], $id)) {
                throw new InvalidArgumentException(
                    "La licencia '{$data['numero_licencia']}' ya está registrada por otro cliente."
                );
            }
        }

        return $this->repository->update($id, $data)->toArray();
    }

    public function deleteCustomer(int $id): void
    {
        if ($this->repository->findById($id) === null) {
            throw new RuntimeException("Cliente con ID $id no encontrado.", 404);
        }
        $this->repository->delete($id);
    }

    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El correo '$email' no tiene formato válido.");
        }
    }
}
