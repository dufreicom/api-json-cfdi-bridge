<?php

declare(strict_types=1);

namespace App\Middleware;

use Dufrei\ApiJsonCfdiBridge\Values\Token;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;

final class AuthorizationMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $token = new Token($this->obtainToken($request));
        $hash = $this->obtainHash();
        if (! $token->verify($hash)) {
            return (new ResponseFactory())
                ->createResponse(401)
                ->withHeader('WWW-Authenticate', 'Bearer');
        }
        return $handler->handle($request);
    }

    public function obtainHash(): string
    {
        $hash = $_ENV['AUTHORIZATION_TOKEN'] ?? '';
        if (! is_string($hash)) {
            return ''; // @codeCoverageIgnore
        }

        return $hash;
    }

    public function obtainToken(Request $request): string
    {
        $header = $request->getHeaderLine('Authorization');
        if (! str_starts_with($header, 'Bearer')) {
            return '';
        }

        return substr($header, 7);
    }
}
