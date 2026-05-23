<?php

declare(strict_types=1);

use App\Config\DatabaseConnection;
use App\Controllers\DevolucionController;
use App\Controllers\ReservationController;
use App\Repositories\DevolucionRepository;
use App\Repositories\ReservationRepository;
use App\Services\DevolucionService;
use App\Services\ReservationService;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([

        ReservationRepository::class => function (ContainerInterface $c): ReservationRepository {
            return new ReservationRepository(DatabaseConnection::getInstance());
        },
        ReservationService::class => function (ContainerInterface $c): ReservationService {
            return new ReservationService($c->get(ReservationRepository::class));
        },
        ReservationController::class => function (ContainerInterface $c): ReservationController {
            return new ReservationController($c->get(ReservationService::class));
        },

        DevolucionRepository::class => function (ContainerInterface $c): DevolucionRepository {
            return new DevolucionRepository(DatabaseConnection::getInstance());
        },
        DevolucionService::class => function (ContainerInterface $c): DevolucionService {
            return new DevolucionService($c->get(DevolucionRepository::class));
        },
        DevolucionController::class => function (ContainerInterface $c): DevolucionController {
            return new DevolucionController($c->get(DevolucionService::class));
        },

    ]);
};
