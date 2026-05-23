<?php

declare(strict_types=1);

use App\Config\DatabaseConnection;
use App\Controllers\VehicleController;
use App\Repositories\VehicleRepository;
use App\Services\VehicleService;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([

        VehicleRepository::class => function (ContainerInterface $c): VehicleRepository {
            return new VehicleRepository(DatabaseConnection::getInstance());
        },

        VehicleService::class => function (ContainerInterface $c): VehicleService {
            return new VehicleService($c->get(VehicleRepository::class));
        },

        VehicleController::class => function (ContainerInterface $c): VehicleController {
            return new VehicleController($c->get(VehicleService::class));
        },

    ]);
};
