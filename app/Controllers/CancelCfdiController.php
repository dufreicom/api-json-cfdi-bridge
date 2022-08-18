<?php

declare(strict_types=1);

namespace App\Controllers;

use Dufrei\ApiJsonCfdiBridge\Factory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rakit\Validation\Validator;
use RuntimeException;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\UploadedFile;
use Exception;
use Throwable;

final class CancelCfdiController
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
            'cfdi' => ['required'],
            'certificate' => ['required'],
            'privatekey' => ['required'],
            'passphrase' => ['present'],
            'finkok-username' => ['present'],
            'finkok-password' => ['present'],
            'finkok-production' => ['present']
        ]);
        $validation->setAliases([
            'cfdi' => 'json input',
            'certificate' => 'certificate content',
            'privatekey' => 'private key content',
            'passphrase' => 'private key passphrase',
            'finkok-username' => 'finkok username',
            'finkok-password' => 'finkok password',
            'finkok-production' => 'finkok production'
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

        try{
            $result = $this -> actionFactory -> cancelCfdis($inputs);
        } catch(Exception $exception){
            return $this->executionError($response, $exception);
        }

        return $this->jsonResponse($response, 200, (object) [
            'statusCode' => $result->statusCode(),
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
