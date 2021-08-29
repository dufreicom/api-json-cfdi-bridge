<?php

declare(strict_types=1);

namespace App\Tests;

use League\Container\Container;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;
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

    protected function getTestingToken(): string
    {
        $token = $_ENV['AUTHORIZATION_TOKEN_PLAIN'] ?? null;
        if (! is_string($token) || '' === $token) {
            throw new LogicException('Token for testing must be environment defined');
        }
        return $token;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array<string, string|string[]> $headers
     * @return ServerRequestInterface
     */
    protected function createRequest(string $method, string $uri, array $headers = []): ServerRequestInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest($method, $uri);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        return $request;
    }

    /**
     * @param string $method
     * @param string $action
     * @param string $authorizationToken
     * @param string[] $inputs
     * @return ServerRequestInterface
     */
    protected function createFormRequest(
        string $method,
        string $action,
        string $authorizationToken = '',
        array $inputs = []
    ): ServerRequestInterface {
        return $this->createRequest($method, $action, array_filter([
            'Authorization' => ($authorizationToken) ? "Bearer $authorizationToken" : null,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]))->withParsedBody($inputs);
    }

    protected function createResponse(int $code = 200): ResponseInterface
    {
        return (new ResponseFactory())->createResponse($code);
    }
}
