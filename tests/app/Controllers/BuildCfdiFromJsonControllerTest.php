<?php

declare(strict_types=1);

namespace App\Tests\Controllers;

use App\Controllers\BuildCfdiFromJsonController;
use App\Tests\TestCase;
use Dufrei\ApiJsonCfdiBridge\Config;
use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\StampService\StampErrors;
use Dufrei\ApiJsonCfdiBridge\StampService\StampException;
use Dufrei\ApiJsonCfdiBridge\Tests\Fakes\FakeFactory;
use Dufrei\ApiJsonCfdiBridge\Tests\Fakes\FakeStampService;
use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\Uuid;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @see BuildCfdiFromJsonController
 */
final class BuildCfdiFromJsonControllerTest extends TestCase
{
    private function createValidFormRequestWithJson(string $json): Request
    {
        return $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken(), [
            'json' => $json,
            'certificate' => $this->fileContents('fake-csd/EKU9003173C9.cer'),
            'privatekey' => $this->fileContents('fake-csd/EKU9003173C9.key'),
            'passphrase' => trim($this->fileContents('fake-csd/EKU9003173C9-password.txt')),
        ]);
    }

    private function setUpContainerWithFakeStampService(Cfdi|StampException|null $result = null): void
    {
        $factory = FakeFactory::create();
        $stampService = new FakeStampService(array_filter([$result]));
        $factory->setStampService($stampService);
        $this->getContainer()->add(Factory::class, $factory);
    }

    public function testBuildCfdiFromJsonUsingFakeStampService(): void
    {
        $cfdi = new Cfdi(
            new Uuid('CEE4BE01-ADFA-4DEB-8421-ADD60F0BEDAC'),
            new XmlContent($this->fileContents('stamped.xml')),
        );
        $factory = new FakeFactory($this->getContainer()->get(Config::class));
        $factory->setStampService(new FakeStampService([$cfdi]));

        // override factory to use a preset stamp service
        $container = $this->getContainer();
        $container->add(Factory::class, $factory);

        $request = $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken(), [
            'json' => $this->fileContents('invoice.json'),
            'certificate' => $this->fileContents('fake-csd/EKU9003173C9.cer'),
            'privatekey' => $this->fileContents('fake-csd/EKU9003173C9.key'),
            'passphrase' => trim($this->fileContents('fake-csd/EKU9003173C9-password.txt')),
        ]);

        $response = $this->getApp()->handle($request);
        $responseData = json_decode((string) $response->getBody());

        $this->assertSame(200, $response->getStatusCode());
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

    public function testControllerValidatesJson(): void
    {
        $request = $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken());
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid json', json_decode((string) $response->getBody())->message);
    }

    public function testControllerValidatesCertificate(): void
    {
        $request = $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken(), [
            'json' => '{}',
        ]);
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid certificate', json_decode((string) $response->getBody())->message);
    }

    public function testControllerValidatesPrivateKey(): void
    {
        $request = $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken(), [
            'json' => '{}',
            'certificate' => 'CER',
        ]);
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid private key', json_decode((string) $response->getBody())->message);
    }

    public function testControllerValidatesCredential(): void
    {
        $request = $this->createFormRequest('POST', '/build-cfdi-from-json', $this->getTestingToken(), [
            'json' => '{}',
            'certificate' => 'CER',
            'privatekey' => 'KEY',
        ]);
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(
            'Unable to create a credential using certificate, private key and passphrase',
            json_decode((string) $response->getBody())->message
        );
    }

    public function testUnableToConvert(): void
    {
        $this->setUpContainerWithFakeStampService();
        $request = $this->createValidFormRequestWithJson('invalid json');
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Unable to parse JSON', json_decode((string) $response->getBody())->message);
    }

    public function testUnableToSignXml(): void
    {
        $this->setUpContainerWithFakeStampService();
        // replace issuer rfc to produce error
        $json = str_replace('EKU9003173C9', 'AAA010101AAA', $this->fileContents('invoice.json'));
        $request = $this->createValidFormRequestWithJson($json);
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('EKU9003173C9', json_decode((string) $response->getBody())->message);
    }

    public function testUnableToStampCfdi(): void
    {
        $stampException = new StampException('Fake message', new StampErrors());
        $this->setUpContainerWithFakeStampService($stampException);
        $request = $this->createValidFormRequestWithJson($this->fileContents('invoice.json'));
        $response = $this->getApp()->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Fake message', json_decode((string) $response->getBody())->message);
    }
}
