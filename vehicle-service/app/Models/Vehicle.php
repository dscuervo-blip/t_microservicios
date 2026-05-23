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
        public readonly ?string $placa      = null,
        public readonly ?string $categoria  = null,
        public readonly string  $estado     = 'disponible',
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null
    ) {}

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'marca'      => $this->marca,
            'modelo'     => $this->modelo,
            'anio'       => $this->anio,
            'placa'      => $this->placa,
            'categoria'  => $this->categoria,
            'estado'     => $this->estado,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id:         isset($data['id']) ? (int) $data['id'] : null,
            marca:      trim($data['marca'] ?? ''),
            modelo:     trim($data['modelo'] ?? ''),
            anio:       (int) ($data['anio'] ?? 0),
            placa:      isset($data['placa'])     ? trim($data['placa'])     : null,
            categoria:  isset($data['categoria']) ? trim($data['categoria']) : null,
            estado:     $data['estado'] ?? 'disponible',
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null
        );
    }
}
