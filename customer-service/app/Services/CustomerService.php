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
        $customers = $this->repository->findAll();

        return array_map(fn(Customer $c) => $c->toArray(), $customers);
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

        $customers = $this->repository->search($query);

        return array_map(fn(Customer $c) => $c->toArray(), $customers);
    }

    public function createCustomer(array $data): array
    {
        // Solo nombre es obligatorio (refleja la BD)
        if (empty($data['nombre'])) {
            throw new InvalidArgumentException("El campo 'nombre' es obligatorio.");
        }

        if (!empty($data['correo'])) {
            $this->validateEmail($data['correo']);

            if ($this->repository->emailExists($data['correo'])) {
                throw new InvalidArgumentException(
                    "El correo '{$data['correo']}' ya está registrado."
                );
            }
        }

        if (!empty($data['numero_licencia'])) {
            if ($this->repository->licenciaExists($data['numero_licencia'])) {
                throw new InvalidArgumentException(
                    "El número de licencia '{$data['numero_licencia']}' ya está registrado."
                );
            }
        }

        $customer = Customer::fromArray($data);

        return $this->repository->create($customer)->toArray();
    }

    public function updateCustomer(int $id, array $data): array
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new RuntimeException("Cliente con ID $id no encontrado.", 404);
        }

        if (!empty($data['correo'])) {
            $this->validateEmail($data['correo']);

            if ($this->repository->emailExists($data['correo'], $id)) {
                throw new InvalidArgumentException(
                    "El correo '{$data['correo']}' ya está registrado por otro cliente."
                );
            }
        }

        if (!empty($data['numero_licencia'])) {
            if ($this->repository->licenciaExists($data['numero_licencia'], $id)) {
                throw new InvalidArgumentException(
                    "El número de licencia '{$data['numero_licencia']}' ya está registrado por otro cliente."
                );
            }
        }

        $updated = $this->repository->update($id, $data);

        return $updated->toArray();
    }

    public function deleteCustomer(int $id): void
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new RuntimeException("Cliente con ID $id no encontrado.", 404);
        }

        $this->repository->delete($id);
    }

    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                "El correo '$email' no tiene un formato válido."
            );
        }
    }
}
