<?php

declare(strict_types=1);

use App\Config\DatabaseConnection;
use App\Controllers\ReservationController;
use App\Repositories\ReservationRepository;
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

    ]);
};
