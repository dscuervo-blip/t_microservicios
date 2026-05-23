<?php

declare(strict_types=1);

use App\Config\DatabaseConnection;
use App\Controllers\CustomerController;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([

        CustomerRepository::class => function (ContainerInterface $c): CustomerRepository {
            return new CustomerRepository(DatabaseConnection::getInstance());
        },

        CustomerService::class => function (ContainerInterface $c): CustomerService {
            return new CustomerService($c->get(CustomerRepository::class));
        },

        CustomerController::class => function (ContainerInterface $c): CustomerController {
            return new CustomerController($c->get(CustomerService::class));
        },

    ]);
};
