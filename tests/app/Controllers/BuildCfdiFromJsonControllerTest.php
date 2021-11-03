<?php

declare(strict_types=1);

namespace App\Tests\Controllers;

use App\Controllers\BuildCfdiFromJsonController;
use App\Tests\TestCase;
use Dufrei\ApiJsonCfdiBridge\StampService\FinkokStampService;
use Dufrei\ApiJsonCfdiBridge\StampService\StampErrors;
use Dufrei\ApiJsonCfdiBridge\StampService\StampException;
use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\Uuid;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;
use Exception;
use PhpCfdi\Finkok\QuickFinkok;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use stdClass;

/**
 * @see BuildCfdiFromJsonController
 */
final class BuildCfdiFromJsonControllerTest extends TestCase
{
    use ContainerWithFakeStampServiceTrait;

    private function createValidFormRequestWithJson(string $json): Request
    {
        return $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken(), [
            'json' => $json,
            'certificate' => $this->fileContents('fake-csd/EKU9003173C9.cer'),
            'privatekey' => $this->fileContents('fake-csd/EKU9003173C9.key'),
            'passphrase' => trim($this->fileContents('fake-csd/EKU9003173C9-password.txt')),
        ]);
    }

    public function testBuildCfdiFromJsonUsingFakeStampService(): void
    {
        $cfdi = new Cfdi(
            new Uuid('CEE4BE01-ADFA-4DEB-8421-ADD60F0BEDAC'),
            new XmlContent($this->fileContents('stamped.xml')),
        );
        $this->setUpContainerWithPedefinedStampServiceResponse($cfdi);
        $request = $this->createValidFormRequestWithJson($this->fileContents('invoice.json'));
        $response = $this->getApp()->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $responseData = $this->jsonResponseBodyToStdClass($response);
        $this->assertStringEqualsFile($this->filePath('converted.xml'), $responseData->converted);
        $this->assertStringEqualsFile($this->filePath('sourcestring.txt'), $responseData->sourcestring);
        $this->assertStringEqualsFile($this->filePath('signed.xml'), $responseData->precfdi);
        $this->assertEquals($cfdi->getUuid(), $responseData->uuid);
        $this->assertEquals($cfdi->getXml(), $responseData->xml);
    }

    public function testControllerAccessUsesToken(): void
    {
        $request = $this->createFormRequest('POST', '/build-cfdi-from-json');
        $response = $this->getApp()->handle($request);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testValidatesNoInputs(): void
    {
        $request = $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken());
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $responseData = $this->jsonResponseBodyToStdClass($response);
        $this->assertSame('Invalid input', $responseData->message);
        $this->assertSame('The json input is required', $responseData->errors->json);
        $this->assertSame('The certificate content is required', $responseData->errors->certificate);
        $this->assertSame('The private key content is required', $responseData->errors->privatekey);
        $this->assertSame('The private key passphrase must be present', $responseData->errors->passphrase);
    }

    public function testValidatesJsonInput(): void
    {
        $this->setUpContainerWithPedefinedStampServiceResponse();
        $request = $this->createValidFormRequestWithJson('invalid json');
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $responseData = $this->jsonResponseBodyToStdClass($response);
        $this->assertSame('Invalid input', $responseData->message);
        $this->assertSame('The json input must be a valid JSON string', $responseData->errors->json);
    }

    public function testControllerValidatesCredential(): void
    {
        $request = $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken(), [
            'json' => '{}',
            'certificate' => 'CER',
            'privatekey' => 'KEY',
            'passphrase' => '',
        ]);
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $responseData = $this->jsonResponseBodyToStdClass($response);
        $this->assertSame('Invalid input', $responseData->message);
        $this->assertSame(
            'Unable to create a credential using certificate, private key and passphrase',
            $responseData->errors[0],
        );
    }

    public function testUnableToSignXml(): void
    {
        $this->setUpContainerWithPedefinedStampServiceResponse();
        // replace issuer rfc to produce error
        $json = str_replace('EKU9003173C9', 'AAA010101AAA', $this->fileContents('invoice.json'));
        $request = $this->createValidFormRequestWithJson($json);
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $responseData = $this->jsonResponseBodyToStdClass($response);
        $this->assertSame('Invalid input', $responseData->message);
        $this->assertStringContainsString('EKU9003173C9', $responseData->errors[0]);
    }

    public function testUnableToStampCfdi(): void
    {
        $this->setUpContainerWithPedefinedStampServiceResponse(
            new StampException('Fake message', new StampErrors()),
        );
        $request = $this->createValidFormRequestWithJson($this->fileContents('invoice.json'));
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $responseData = $this->jsonResponseBodyToStdClass($response);
        $this->assertSame('Invalid input', $responseData->message);
        $this->assertStringContainsString('Fake message', $responseData->errors[0]);
    }

    public function testServiceError(): void
    {
        $remoteException = new Exception('ups something happened', previous: new Exception('deep exception'));

        /** @var QuickFinkok&MockObject $quickFinkok */
        $quickFinkok = $this->createMock(QuickFinkok::class);
        $quickFinkok->expects($this->once())
            ->method('stamp')
            ->willThrowException($remoteException);

        $this->setUpContainerWithFakeStampService(
            new FinkokStampService($quickFinkok),
        );
        $request = $this->createValidFormRequestWithJson($this->fileContents('invoice.json'));
        $response = $this->getApp()->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $responseData = $this->jsonResponseBodyToStdClass($response);
        $this->assertSame('Error on call Finkok stamp', $responseData->message);
        $expectedErrors = [
            'Error on call Finkok stamp',
            'ups something happened',
            'deep exception',
        ];
        $this->assertSame($expectedErrors, $responseData->errors);
    }

    private function jsonResponseBodyToStdClass(ResponseInterface $response): stdClass
    {
        $responseData = json_decode((string) $response->getBody());
        if (! $responseData instanceof stdClass) {
            throw new \LogicException('Response body should be json parseable as object');
        }
        return $responseData;
    }
}
