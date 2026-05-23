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

// ─── Middleware ───────────────────────────────────────────────────────────────
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(new CorsMiddleware());

$app->addErrorMiddleware(
    displayErrorDetails: true,
    logErrors:           true,
    logErrorDetails:     true
);

// ─── Rutas ───────────────────────────────────────────────────────────────────
(require __DIR__ . '/../app/Routes/customerRoutes.php')($app);

$app->run();
