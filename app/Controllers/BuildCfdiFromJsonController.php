<?php

declare(strict_types=1);

namespace App\Controllers;

use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter\JsonToXmlConvertException;
use Dufrei\ApiJsonCfdiBridge\PreCfdiSigner\UnableToSignXmlException;
use Dufrei\ApiJsonCfdiBridge\StampService\ServiceException;
use Dufrei\ApiJsonCfdiBridge\StampService\StampException;
use Dufrei\ApiJsonCfdiBridge\Values\CredentialCsd;
use Dufrei\ApiJsonCfdiBridge\Values\JsonContent;
use PhpCfdi\Credentials\Credential;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rakit\Validation\Validator;
use RuntimeException;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\UploadedFile;
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
        $inputs = array_merge(
            (array) ($request->getParsedBody() ?? []),
            $this->uploadedFilesToInputs($request->getUploadedFiles()),
        );
        $validator = new Validator();
        $validation = $validator->make($inputs, [
            'json' => ['required', 'json'],
            'certificate' => ['required'],
            'privatekey' => ['required'],
            'passphrase' => ['present'],
        ]);
        $validation->setAliases([
            'json' => 'json input',
            'certificate' => 'certificate content',
            'privatekey' => 'private key content',
            'passphrase' => 'private key passphrase',
        ]);
        $validation->validate();
        if ($validation->fails()) {
            $errors = [];
            foreach ($validation->errors()->toArray() as $name => $error) {
                $errors[$name] = implode(PHP_EOL, $error);
            }
            return $this->validationError($response, $errors);
        }
        $inputs = $validation->getValidData();

        try {
            $credential = Credential::create($inputs['certificate'], $inputs['privatekey'], $inputs['passphrase']);
        } catch (Throwable $exception) {
            return $this->validationError($response, [
                'Unable to create a credential using certificate, private key and passphrase',
                $exception->getMessage(),
            ]);
        }

        $json = new JsonContent($inputs['json']);
        $csd = new CredentialCsd($credential);
        $action = $this->actionFactory->createBuildCfdiFromJsonAction();
        try {
            $result = $action->execute($json, $csd);
        } catch (JsonToXmlConvertException | UnableToSignXmlException $exception) {
            return $this->validationError($response, [$exception->getMessage()]);
        } catch (StampException $exception) {
            $messages = array_merge([$exception->getMessage()], $exception->getErrors()->messages());
            return $this->validationError($response, $messages);
        } catch (ServiceException $exception) {
            return $this->executionError($response, $exception);
        }

        return $this->jsonResponse($response, 200, (object) [
            'converted' => $result->getConvertedXml(),
            'sourcestring' => $result->getPreCfdi()->getSourceString(),
            'precfdi' => $result->getPreCfdi()->getXml(),
            'uuid' => $result->getCfdi()->getUuid(),
            'xml' => $result->getCfdi()->getXml(),
        ]);
    }

    /**
     * @param Response $response
     * @param string[] $errors
     * @return Response
     */
    private function validationError(Response $response, array $errors): Response
    {
        return $this->jsonResponse($response, 400, (object) [
            'message' => 'Invalid input',
            'errors' => $errors,
        ]);
    }

    private function executionError(Response $response, Throwable $exception): Response
    {
        $errors = [];
        for ($current = $exception; null !== $current; $current = $current->getPrevious()) {
            $errors[] = $current->getMessage();
        }

        return $this->jsonResponse($response, 500, (object) [
            'message' => $exception->getMessage(),
            'errors' => $errors,
        ]);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    private function jsonResponse(Response $response, int $status, object $responseData): Response
    {
        $responseStream = $this->streamFactory->createStream(json_encode($responseData, flags: JSON_THROW_ON_ERROR));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($responseStream);
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     * @return array<string, string>
     */
    private function uploadedFilesToInputs(array $uploadedFiles): array
    {
        $inputs = [];
        foreach ($uploadedFiles as $key => $uploadedFile) {
            $inputs[$key] = $this->uploadedFileToContent($uploadedFile);
        }
        return array_filter($inputs);
    }

    private function uploadedFileToContent(UploadedFile $uploadedFile): string
    {
        try {
            if (UPLOAD_ERR_OK !== $uploadedFile->getError()) {
                return '';
            }
            return (string) $uploadedFile->getStream();
        } catch (RuntimeException) {
            return '';
        } finally {
            unlink($uploadedFile->getFilePath());
        }
    }
}
