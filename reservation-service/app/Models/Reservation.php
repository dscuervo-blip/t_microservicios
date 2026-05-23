<?php

declare(strict_types=1);

namespace App\Models;

class Reservation
{
    public function __construct(
        public readonly ?int    $id,
        public readonly int     $cliente_id,
        public readonly int     $vehiculo_id,
        public readonly string  $fecha_inicio,
        public readonly string  $fecha_fin,
        public readonly string  $estado,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'id'           => $this->id,
            'cliente_id'   => $this->cliente_id,
            'vehiculo_id'  => $this->vehiculo_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin'    => $this->fecha_fin,
            'estado'       => $this->estado,
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
            id:           isset($data['id']) ? (int) $data['id'] : null,
            cliente_id:   (int) $data['cliente_id'],
            vehiculo_id:  (int) $data['vehiculo_id'],
            fecha_inicio: $data['fecha_inicio'],
            fecha_fin:    $data['fecha_fin'],
            // Estado real según ENUM de BD: activa | completada | cancelada
            estado:       $data['estado'] ?? 'activa',
            created_at:   $data['created_at'] ?? null,
            updated_at:   $data['updated_at'] ?? null
        );
    }
}
