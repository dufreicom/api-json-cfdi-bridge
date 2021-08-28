<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Integration\Actions;

use DOMElement;
use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\PreCfdiSigner\UnableToSignXml;
use Dufrei\ApiJsonCfdiBridge\Tests\Fakes\FakeCsd;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;

final class SignXmlActionTest extends TestCase
{
    public function testSignPutAllRequiredInformation(): void
    {
        $factory = Factory::create();
        $action = $factory->createSignXmlAction();

        $xml = new XmlContent($this->fileContents('converted.xml'));
        $rfc = 'EKU9003173C9';
        $certificateNumber = '30001000000400002434';
        $certificateContents = base64_encode(hash('sha256', 'CERTIFICADO', true)) ?: 'CERTIFICADO';
        $signature = base64_encode(hash('sha256', 'SELLO', true)) ?: 'SELLO';
        $csd = new FakeCsd($rfc, $certificateContents, $certificateNumber, true, true, fn (): string => $signature);

        $result = $action->execute($xml, $csd);
        $document = $result->getPreCfdi()->getXml()->toDocument();
        /** @var DOMElement $comprobante */
        $comprobante = $document->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
        /** @var DOMElement $emisor */
        $emisor = $document->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);

        $this->assertSame($certificateContents, $comprobante->getAttribute('Certificado'));
        $this->assertSame($certificateNumber, $comprobante->getAttribute('NoCertificado'));
        $this->assertSame($signature, $comprobante->getAttribute('Sello'));
        $this->assertSame($rfc, $emisor->getAttribute('Rfc'));
    }

    public function testSignWithInvalidCsdType(): void
    {
        $factory = Factory::create();
        $action = $factory->createSignXmlAction();

        $xml = new XmlContent($this->fileContents('converted.xml'));
        $rfc = 'EKU9003173C9';
        $certificateNumber = '30001000000400002434';
        $csd = new FakeCsd($rfc, 'MIIFuzCCA6...', $certificateNumber, false, true);

        $this->expectException(UnableToSignXml::class);
        $this->expectExceptionMessage("The certificate $certificateNumber from $rfc is not a CSD");
        $action->execute($xml, $csd);
    }

    public function testSignWithInvalidCsdExpired(): void
    {
        $factory = Factory::create();
        $action = $factory->createSignXmlAction();

        $xml = new XmlContent($this->fileContents('converted.xml'));
        $rfc = 'EKU9003173C9';
        $certificateNumber = '30001000000400002434';
        $csd = new FakeCsd($rfc, 'MIIFuzCCA6...', $certificateNumber, true, false);

        $this->expectException(UnableToSignXml::class);
        $this->expectExceptionMessage("The certificate $certificateNumber from $rfc is expired");
        $action->execute($xml, $csd);
    }
}
