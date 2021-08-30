<?php

declare(strict_types=1);

use App\Controllers\BuildCfdiFromJsonController;
use App\Middleware\AuthorizationMiddleware;
use Dufrei\ApiJsonCfdiBridge\Config;
use Dufrei\ApiJsonCfdiBridge\ConfigBuilder;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Slim\App;
use Slim\Factory\AppFactory;

return (function (): App {
    $container = new Container();
    $container->delegate(new ReflectionContainer());
    $container->add('environment', static fn () => $_ENV);
    $container->add(Config::class, static fn (array $environment): Config => (new ConfigBuilder($environment))->build())
        ->addArgument($container->get('environment'));

    $app = AppFactory::create(container: $container);

    $app->add($container->get(AuthorizationMiddleware::class));

    $errorMiddleware = $app->addErrorMiddleware(displayErrorDetails: true, logErrors: false, logErrorDetails: false);
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->forceContentType('application/json');

    $app->post('/build-cfdi-from-json', BuildCfdiFromJsonController::class);

    return $app;
})();
