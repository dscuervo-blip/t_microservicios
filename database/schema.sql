CREATE DATABASE IF NOT EXISTS alquiler_vehiculos
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE alquiler_vehiculos;

CREATE TABLE IF NOT EXISTS vehiculos (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    marca      VARCHAR(60)  NOT NULL,
    modelo     VARCHAR(60)  NOT NULL,
    anio       YEAR         NOT NULL,
    categoria  ENUM('sedan','suv','camioneta','deportivo','furgon') NOT NULL,
    placa      VARCHAR(20)  NOT NULL UNIQUE,
    estado     ENUM('disponible','alquilado','mantenimiento') NOT NULL DEFAULT 'disponible',
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clientes (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(80)  NOT NULL,
    apellido         VARCHAR(80)  NOT NULL,
    email            VARCHAR(120) NOT NULL UNIQUE,
    telefono         VARCHAR(20),
    documento        VARCHAR(30)  NOT NULL UNIQUE,
    numero_licencia  VARCHAR(30)  NOT NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservas (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id  INT UNSIGNED NOT NULL,
    cliente_id   INT UNSIGNED NOT NULL,
    fecha_inicio DATE         NOT NULL,
    fecha_fin    DATE         NOT NULL,
    estado       ENUM('pendiente','activa','completada','cancelada') NOT NULL DEFAULT 'pendiente',
    valor_total  DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id),
    FOREIGN KEY (cliente_id)  REFERENCES clientes(id)
);

CREATE TABLE IF NOT EXISTS devoluciones (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id       INT UNSIGNED NOT NULL,
    vehiculo_id      INT UNSIGNED NOT NULL,
    cliente_id       INT UNSIGNED NOT NULL,
    fecha_devolucion DATE         NOT NULL,
    km_recorridos    INT UNSIGNED NOT NULL DEFAULT 0,
    estado_vehiculo  ENUM('bueno','con_daños') NOT NULL DEFAULT 'bueno',
    observaciones    TEXT,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id)  REFERENCES reservas(id),
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id),
    FOREIGN KEY (cliente_id)  REFERENCES clientes(id)
);
