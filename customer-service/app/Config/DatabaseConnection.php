<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    private static function createConnection(): PDO
    {
        $dsn = 'mysql:host=localhost;dbname=alquiler_vehiculos_db;charset=utf8mb4';

        try {
            return new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new PDOException(
                'Error de conexión a la base de datos: ' . $e->getMessage(),
                (int) $e->getCode()
            );
        }
    }
}
