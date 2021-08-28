<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Integration\Actions;

use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\Tests\Fakes\FakeStampService;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;
use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\JsonContent;
use Dufrei\ApiJsonCfdiBridge\Values\SourceString;
use Dufrei\ApiJsonCfdiBridge\Values\Uuid;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;

final class BuildCfdiFromJsonActionTest extends TestCase
{
    public function testBuildCfdiFromJsonActionUsingFakeFinkokService(): void
    {
        $jsonContent = new JsonContent($this->fileContents('invoice.json'));
        $convertedContent = new XmlContent($this->fileContents('converted.xml'));
        $sourceStringContent = new SourceString($this->fileContents('sourcestring.txt'));
        $signedContent = new XmlContent($this->fileContents('signed.xml'));
        $stampedContent = new XmlContent($this->fileContents('stamped.xml'));
        $cfdi = new Cfdi(
            new Uuid('CEE4BE01-ADFA-4DEB-8421-ADD60F0BEDAC'),
            $stampedContent,
        );

        $factory = Factory::create();
        $stampService = new FakeStampService([$cfdi]);
        $action = $factory->createBuildCfdiFromJsonAction(stampService: $stampService);
        $result = $action->execute($jsonContent, $this->createCsdForTesting());

        $this->assertSame($jsonContent, $result->getJson());

        // file_put_contents($this->filePath('converted.xml'), $result->getConvertedXml());
        // $convertedContent = new XmlContent($this->fileContents('converted.xml'));
        $this->assertEquals($convertedContent->toDocument(), $result->getConvertedXml()->toDocument());

        // file_put_contents($this->filePath('sourcestring.txt'), $result->getPreCfdi()->getSourceString());
        // $sourceStringContent = new SourceString($this->fileContents('sourcestring.txt'));
        $this->assertEquals($sourceStringContent, $result->getPreCfdi()->getSourceString());

        // file_put_contents($this->filePath('signed.xml'), $result->getPreCfdi()->getXml());
        // $signedContent = new XmlContent($this->fileContents('signed.xml'));
        $this->assertEquals($signedContent->toDocument(), $result->getPreCfdi()->getXml()->toDocument());

        // this object has been mocked
        $this->assertSame($cfdi, $result->getCfdi());
    }
}
