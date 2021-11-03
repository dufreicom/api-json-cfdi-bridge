<?php

declare(strict_types=1);

namespace App\Tests\Controllers;

use App\Controllers\BuildCfdiFromJsonController;
use App\Tests\TestCase;
use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\Uuid;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;
use LogicException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

/**
 * @see BuildCfdiFromJsonController
 */
final class BuildCfdiFromJsonControllerUsingFilesTest extends TestCase
{
    use ContainerWithFakeStampServiceTrait;

    private function createValidFormRequestFilesWithJson(string $jsonFile): Request
    {
        $request = $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken(), [
            'passphrase' => trim($this->fileContents('fake-csd/EKU9003173C9-password.txt')),
        ]);

        $request = $request->withUploadedFiles([
            'json' => $this->createTemporaryUploadedFile($jsonFile),
            'certificate' => $this->createTemporaryUploadedFile($this->filePath('fake-csd/EKU9003173C9.cer')),
            'privatekey' => $this->createTemporaryUploadedFile($this->filePath('fake-csd/EKU9003173C9.key')),
        ]);

        return $request->withHeader('Content-Type', 'multipart/form-data');
    }

    private function createTemporaryUploadedFile(string $sourceFileName): UploadedFile
    {
        $tmpname = tempnam('', '');
        if (false === $tmpname) {
            throw new LogicException('Unable to create a temporary file');
        }
        if (! copy($sourceFileName, $tmpname)) {
            throw new LogicException('Unable to copy the upload file source to a temporary file');
        }
        return new UploadedFile($tmpname, basename($sourceFileName));
    }

    public function testBuildCfdiFromJsonWithFilesUsingFakeStampService(): void
    {
        $cfdi = new Cfdi(
            new Uuid('CEE4BE01-ADFA-4DEB-8421-ADD60F0BEDAC'),
            new XmlContent($this->fileContents('stamped.xml')),
        );
        $this->setUpContainerWithPedefinedStampServiceResponse($cfdi);
        $request = $this->createValidFormRequestFilesWithJson($this->filePath('invoice.json'));
        $response = $this->getApp()->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        /** @var UploadedFile $uploadedFile */
        foreach ($request->getUploadedFiles() as $uploadedFile) {
            $this->assertFileDoesNotExist(
                $uploadedFile->getFilePath(),
                "Uploaded file {$uploadedFile->getFilePath()} should be removed",
            );
        }
    }
}
