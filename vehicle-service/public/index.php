<?php

declare(strict_types=1);

use App\Middleware\CorsMiddleware;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// ─── Contenedor de dependencias ──────────────────────────────────────────────
$containerBuilder = new ContainerBuilder();

$dependencies = require __DIR__ . '/../app/Config/dependencies.php';
$dependencies($containerBuilder);

$container = $containerBuilder->build();

// ─── Aplicación Slim ─────────────────────────────────────────────────────────
AppFactory::setContainer($container);
$app = AppFactory::create();

// Base path para XAMPP en subdirectorio
$app->setBasePath(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']));

// ─── Middleware (el último en registrarse se ejecuta primero) ─────────────────
$app->addBodyParsingMiddleware();   // Parsea JSON y form-data
$app->addRoutingMiddleware();       // Habilita el enrutamiento
$app->add(new CorsMiddleware());    // CORS (ejecuta antes que el router)

// Error middleware: el último = más externo
$errorMiddleware = $app->addErrorMiddleware(
    displayErrorDetails: true,   // false en producción
    logErrors:           true,
    logErrorDetails:     true
);

// ─── Rutas ───────────────────────────────────────────────────────────────────
(require __DIR__ . '/../app/Routes/vehicleRoutes.php')($app);

$app->run();
