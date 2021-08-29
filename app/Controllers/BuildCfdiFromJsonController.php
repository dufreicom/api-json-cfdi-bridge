<?php

declare(strict_types=1);

namespace App\Controllers;

use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\Values\CredentialCsd;
use Dufrei\ApiJsonCfdiBridge\Values\JsonContent;
use PhpCfdi\Credentials\Credential;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\StreamFactory;

final class BuildCfdiFromJsonController
{
    public function __construct(
        private Factory $actionFactory,
        private StreamFactory $streamFactory,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $inputs = $request->getParsedBody();

        $json = new JsonContent(strval($inputs['json'] ?? ''));
        $credential = Credential::create(
            strval($inputs['certificate'] ?? ''),
            strval($inputs['privatekey'] ?? ''),
            strval($inputs['passphrase'] ?? ''),
        );
        $csd = new CredentialCsd($credential);

        $action = $this->actionFactory->createBuildCfdiFromJsonAction();
        $result = $action->execute($json, $csd);
        $responseData = [
            'converted' => $result->getConvertedXml(),
            'sourcestring' => $result->getPreCfdi()->getSourceString(),
            'precfdi' => $result->getPreCfdi()->getXml(),
            'uuid' => $result->getCfdi()->getUuid(),
            'xml' => $result->getCfdi()->getXml(),
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $responseBody = $this->streamFactory->createStream(json_encode($responseData));
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($responseBody);
    }
}
