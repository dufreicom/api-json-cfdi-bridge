<?php

declare(strict_types=1);

namespace App\Controllers;

use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter\JsonToXmlConvertException;
use Dufrei\ApiJsonCfdiBridge\PreCfdiSigner\UnableToSignXml;
use Dufrei\ApiJsonCfdiBridge\StampService\StampException;
use Dufrei\ApiJsonCfdiBridge\Values\CredentialCsd;
use Dufrei\ApiJsonCfdiBridge\Values\JsonContent;
use PhpCfdi\Credentials\Credential;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Psr7\Factory\StreamFactory;
use Throwable;

final class BuildCfdiFromJsonController
{
    public function __construct(
        private Factory $actionFactory,
        private StreamFactory $streamFactory,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $inputJson = $this->requestStringValue($request, 'json');
        if ('' === $inputJson) {
            throw (new HttpBadRequestException($request))->setTitle('Invalid json');
        }

        $inputCertificate = $this->requestStringValue($request, 'certificate');
        if ('' === $inputCertificate) {
            throw (new HttpBadRequestException($request))->setTitle('Invalid certificate');
        }

        $inputPrivateKey = $this->requestStringValue($request, 'privatekey');
        if ('' === $inputPrivateKey) {
            throw (new HttpBadRequestException($request))->setTitle('Invalid private key');
        }

        $inputPassPhrase = $this->requestStringValue($request, 'passphrase');

        try {
            $credential = Credential::create($inputCertificate, $inputPrivateKey, $inputPassPhrase);
        } catch (Throwable $exception) {
            throw (new HttpBadRequestException($request, previous: $exception))
                ->setTitle('Unable to create a credential using certificate, private key and passphrase');
        }

        $json = new JsonContent($inputJson);
        $csd = new CredentialCsd($credential);
        $action = $this->actionFactory->createBuildCfdiFromJsonAction();
        try {
            $result = $action->execute($json, $csd);
        } catch (JsonToXmlConvertException|UnableToSignXml|StampException $exception) {
            throw (new HttpBadRequestException($request, previous: $exception))->setTitle($exception->getMessage());
        }
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

    private function requestStringValue(Request $request, string $input)
    {
        $value = $request->getParsedBody()[$input] ?? null;
        if (! is_string($value)) {
            return '';
        }
        return $value;
    }
}
