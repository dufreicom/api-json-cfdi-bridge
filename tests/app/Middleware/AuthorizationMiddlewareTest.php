<?php

declare(strict_types=1);

namespace App\Tests\Middleware;

use App\Middleware\AuthorizationMiddleware;
use App\Tests\TestCase;
use Dufrei\ApiJsonCfdiBridge\Values\Token;
use Psr\Http\Message\ResponseInterface;

final class AuthorizationMiddlewareTest extends TestCase
{
    public function testHandleAuthorized(): void
    {
        $predefinedResponse = $this->createResponse(201);
        $token = new Token($this->getTestingToken());
        $application = $this->getApp();
        $application
            ->get('/test/authorization', fn (): ResponseInterface => $predefinedResponse)
            ->add(AuthorizationMiddleware::class);

        $request = $this->createRequest('GET', '/test/authorization', [
            'Authorization' => "Bearer {$token->getToken()}",
        ]);
        $response = $application->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testHandleIncorrectToken(): void
    {
        $predefinedResponse = $this->createResponse(201);
        $incorrectToken = Token::createRandom();
        $application = $this->getApp();
        $application
            ->get('/test/authorization', fn (): ResponseInterface => $predefinedResponse)
            ->add(AuthorizationMiddleware::class);

        $request = $this->createRequest('GET', '/test/authorization', [
            'Authorization' => "Bearer {$incorrectToken->getToken()}",
        ]);
        $response = $application->handle($request);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testHandleMissingToken(): void
    {
        $predefinedResponse = $this->createResponse(201);
        $application = $this->getApp();
        $application
            ->get('/test/authorization', fn (): ResponseInterface => $predefinedResponse)
            ->add(AuthorizationMiddleware::class);

        $request = $this->createRequest('GET', '/test/authorization');
        $response = $application->handle($request);

        $this->assertSame(401, $response->getStatusCode());
    }
}
