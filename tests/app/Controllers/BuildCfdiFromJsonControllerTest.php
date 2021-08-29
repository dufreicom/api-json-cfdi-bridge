<?php

declare(strict_types=1);

namespace App\Tests\Controllers;

use App\Controllers\BuildCfdiFromJsonController;
use App\Tests\TestCase;
use Dufrei\ApiJsonCfdiBridge\Config;
use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\Tests\Fakes\FakeFactory;
use Dufrei\ApiJsonCfdiBridge\Tests\Fakes\FakeStampService;
use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\Uuid;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;

/**
 * @see BuildCfdiFromJsonController
 */
final class BuildCfdiFromJsonControllerTest extends TestCase
{
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

        $request = $this->createFormRequest('POST', '/build-cfdi-from-json', [
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
}
