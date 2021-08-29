<?php

declare(strict_types=1);

namespace App\Tests;

use League\Container\Container;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;

abstract class TestCase extends \Dufrei\ApiJsonCfdiBridge\Tests\TestCase
{
    private App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = require __DIR__ . '/../../bootstrap.php';
    }

    protected function getApp(): App
    {
        return $this->app;
    }

    protected function getContainer(): Container
    {
        $container = $this->app->getContainer();
        if (! $container instanceof Container) {
            throw new LogicException('Container is not set up as ' . Container::class);
        }
        return $container;
    }

    /**
     * @param string $method
     * @param string $action
     * @param mixed $inputs
     * @return ServerRequestInterface
     */
    protected function createFormRequest(string $method, string $action, $inputs): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest($method, $action)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withParsedBody($inputs);
    }
}
