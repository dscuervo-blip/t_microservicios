<?php

declare(strict_types=1);

namespace App\Models;

class Customer
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $nombre,
        public readonly ?string $apellido        = null,
        public readonly ?string $email           = null,
        public readonly ?string $telefono        = null,
        public readonly ?string $documento       = null,
        public readonly ?string $numero_licencia = null,
        public readonly ?string $created_at      = null,
        public readonly ?string $updated_at      = null
    ) {}

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'nombre'          => $this->nombre,
            'apellido'        => $this->apellido,
            'email'           => $this->email,
            'correo'          => $this->email,
            'telefono'        => $this->telefono,
            'documento'       => $this->documento,
            'numero_licencia' => $this->numero_licencia,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }

    public static function fromArray(array $data): self
    {
        // acepta tanto 'email' como 'correo' del formulario
        $email = $data['email'] ?? $data['correo'] ?? null;

        return new self(
            id:               isset($data['id']) ? (int) $data['id'] : null,
            nombre:           trim($data['nombre'] ?? ''),
            apellido:         isset($data['apellido'])  ? trim($data['apellido'])  : null,
            email:            $email !== null ? trim($email) : null,
            telefono:         isset($data['telefono'])  ? trim($data['telefono'])  : null,
            documento:        isset($data['documento']) ? trim($data['documento']) : null,
            numero_licencia:  isset($data['numero_licencia']) ? trim($data['numero_licencia']) : null,
            created_at:       $data['created_at'] ?? null,
            updated_at:       $data['updated_at'] ?? null
        );
    }
}
