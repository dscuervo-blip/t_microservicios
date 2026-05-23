<?php

declare(strict_types=1);

namespace App\Models;

class Customer
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $nombre,
        public readonly ?string $telefono        = null,
        public readonly ?string $correo          = null,
        public readonly ?string $numero_licencia = null,
        public readonly ?string $created_at      = null,
        public readonly ?string $updated_at      = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'id'              => $this->id,
            'nombre'          => $this->nombre,
            'telefono'        => $this->telefono,
            'correo'          => $this->correo,
            'numero_licencia' => $this->numero_licencia,
        ];

        if ($this->created_at !== null) {
            $data['created_at'] = $this->created_at;
        }
        if ($this->updated_at !== null) {
            $data['updated_at'] = $this->updated_at;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id:               isset($data['id']) ? (int) $data['id'] : null,
            nombre:           trim($data['nombre']),
            telefono:         isset($data['telefono'])        ? trim($data['telefono'])        : null,
            correo:           isset($data['correo'])          ? trim($data['correo'])          : null,
            numero_licencia:  isset($data['numero_licencia']) ? trim($data['numero_licencia']) : null,
            created_at:       $data['created_at'] ?? null,
            updated_at:       $data['updated_at'] ?? null
        );
    }
}
