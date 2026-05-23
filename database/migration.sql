-- ============================================================
-- MIGRACIÓN: Actualizar alquiler_vehiculos_db
-- Ejecutar en phpMyAdmin o MySQL CLI
-- ============================================================

USE alquiler_vehiculos_db;

-- ── vehiculos: agregar columna placa ────────────────────────
ALTER TABLE vehiculos
    ADD COLUMN IF NOT EXISTS placa VARCHAR(20) NULL UNIQUE AFTER anio;

-- ── clientes: agregar apellido y documento ──────────────────
ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS apellido  VARCHAR(100) NULL AFTER nombre,
    ADD COLUMN IF NOT EXISTS documento VARCHAR(50)  NULL AFTER apellido;

-- ── reservas: agregar valor_total ───────────────────────────
ALTER TABLE reservas
    ADD COLUMN IF NOT EXISTS valor_total DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER estado;

-- ── crear tabla devoluciones ─────────────────────────────────
CREATE TABLE IF NOT EXISTS devoluciones (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id       INT NOT NULL,
    vehiculo_id      INT NOT NULL,
    cliente_id       INT NOT NULL,
    fecha_devolucion DATE NOT NULL,
    km_recorridos    INT UNSIGNED NOT NULL DEFAULT 0,
    estado_vehiculo  ENUM('bueno','con_daños') NOT NULL DEFAULT 'bueno',
    observaciones    TEXT,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id)  REFERENCES reservas(id)   ON DELETE CASCADE,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id)  ON DELETE CASCADE,
    FOREIGN KEY (cliente_id)  REFERENCES clientes(id)   ON DELETE CASCADE
);
