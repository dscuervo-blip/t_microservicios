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
        public readonly string  $estado      = 'activa',
        public readonly float   $valor_total = 0.0,
        public readonly ?string $created_at  = null,
        public readonly ?string $updated_at  = null
    ) {}

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'cliente_id'   => $this->cliente_id,
            'vehiculo_id'  => $this->vehiculo_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin'    => $this->fecha_fin,
            'estado'       => $this->estado,
            'valor_total'  => $this->valor_total,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id:           isset($data['id']) ? (int) $data['id'] : null,
            cliente_id:   (int) ($data['cliente_id']  ?? 0),
            vehiculo_id:  (int) ($data['vehiculo_id'] ?? 0),
            fecha_inicio: $data['fecha_inicio'] ?? '',
            fecha_fin:    $data['fecha_fin']    ?? '',
            estado:       $data['estado']       ?? 'activa',
            valor_total:  (float) ($data['valor_total'] ?? 0),
            created_at:   $data['created_at']   ?? null,
            updated_at:   $data['updated_at']   ?? null
        );
    }
}
