<?php

declare(strict_types=1);

namespace App\Models;

class Vehicle
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $marca,
        public readonly string  $modelo,
        public readonly int     $anio,
        public readonly ?string $categoria,
        public readonly string  $estado,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'id'        => $this->id,
            'marca'     => $this->marca,
            'modelo'    => $this->modelo,
            'anio'      => $this->anio,
            'categoria' => $this->categoria,
            'estado'    => $this->estado,
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
            id:         isset($data['id']) ? (int) $data['id'] : null,
            marca:      trim($data['marca']),
            modelo:     trim($data['modelo']),
            anio:       (int) $data['anio'],
            categoria:  isset($data['categoria']) ? trim($data['categoria']) : null,
            estado:     $data['estado'] ?? 'disponible',
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null
        );
    }
}
