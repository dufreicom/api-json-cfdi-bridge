<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Unit\PreCfdiSigner;

use CfdiUtils\CadenaOrigen\CfdiDefaultLocations;
use CfdiUtils\CadenaOrigen\XsltBuilderInterface;
use CfdiUtils\XmlResolver\XmlResolver;
use DOMDocument;
use Dufrei\ApiJsonCfdiBridge\PreCfdiSigner\PreCfdiSigner;
use Dufrei\ApiJsonCfdiBridge\PreCfdiSigner\UnableToSignXml;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class PreCfdiSignerTest extends TestCase
{
    private function createSignerWithMockedDependences(DOMDocument $document): PreCfdiSigner
    {
        $xmlResolver = $this->createMock(XmlResolver::class);
        $xsltBuilder = $this->createMock(XsltBuilderInterface::class);
        return new PreCfdiSigner($document, $xmlResolver, $xsltBuilder);
    }

    public function testPutAllData(): void
    {
        $rfc = 'AAAA010101AAA';
        $certificateValue = 'CERTIFICADO';
        $certificateNumber = '12345678901234567890';
        $signature = 'SELLO';
        $expected = $this->createXmlDocument(
            <<<XML
                <cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3"
                  Certificado="$certificateValue"
                  NoCertificado="$certificateNumber"
                  Sello="$signature"
                  >
                <cfdi:Emisor Rfc="$rfc"/>
                </cfdi:Comprobante>
                XML
        );

        $document = $this->createXmlDocument(
            <<<XML
                <cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3">
                <cfdi:Emisor/>
                </cfdi:Comprobante>
                XML
        );
        $xmlResolver = $this->createMock(XmlResolver::class);
        $xsltBuilder = $this->createMock(XsltBuilderInterface::class);
        $signer = new PreCfdiSigner($document, $xmlResolver, $xsltBuilder);

        $signer->putCertificateNumber($certificateNumber);
        $signer->putCertificateValue($certificateValue);
        $signer->putIssuerRfc($rfc);
        $signer->putSignatureValue($signature);

        $this->assertEquals($expected, $document);
    }

    public function testPutOnNonCfdi33(): void
    {
        $rfc = 'AAAA010101AAA';
        $certificateValue = 'CERTIFICADO';
        $certificateNumber = '12345678901234567890';
        $signature = 'SELLO';
        $expected = $this->createXmlDocument(
            <<<XML
                <cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/2">
                <cfdi:Emisor/>
                </cfdi:Comprobante>
                XML
        );

        $document = $this->createXmlDocument(
            <<<XML
                <cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/2">
                <cfdi:Emisor/>
                </cfdi:Comprobante>
                XML
        );
        $xmlResolver = $this->createMock(XmlResolver::class);
        $xsltBuilder = $this->createMock(XsltBuilderInterface::class);
        $signer = new PreCfdiSigner($document, $xmlResolver, $xsltBuilder);

        $signer->putCertificateNumber($certificateNumber);
        $signer->putCertificateValue($certificateValue);
        $signer->putIssuerRfc($rfc);
        $signer->putSignatureValue($signature);

        $this->assertEquals($expected, $document);
    }

    public function testPutRfcFailsWhenExistsButDifferent(): void
    {
        $sourceRfc = 'AAAA010101AAA';
        $differentRfc = 'XXXX010101XXX';
        $document = $this->createXmlDocument(
            <<<XML
                <cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3">
                <cfdi:Emisor Rfc="$sourceRfc"/>
                </cfdi:Comprobante>
                XML
        );

        $signer = $this->createSignerWithMockedDependences($document);

        $this->expectException(UnableToSignXml::class);
        $this->expectExceptionMessage("The issuer RFC on data $sourceRfc is different from CSD $differentRfc");
        $signer->putIssuerRfc($differentRfc);
    }

    public function testBuildSourceString(): void
    {
        $remoteXsltLocation = CfdiDefaultLocations::XSLT_33;
        $localXsltLocation = '/resources/fake/location.xsd';
        $sourceString = '||3.3||';

        $document = $this->createXmlDocument(
            '<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" Version="3.3"/>',
        );

        /** @var XmlResolver&MockObject $xmlResolver */
        $xmlResolver = $this->createMock(XmlResolver::class);
        $xmlResolver->expects($this->once())
            ->method('resolve')
            ->with($remoteXsltLocation)
            ->willReturn($localXsltLocation);

        /** @var XsltBuilderInterface&MockObject $xsltBuilder */
        $xsltBuilder = $this->createMock(XsltBuilderInterface::class);
        $xsltBuilder->expects($this->once())
            ->method('build')
            ->with($document->saveXML(), $localXsltLocation)
            ->willReturn($sourceString);
        $signer = new PreCfdiSigner($document, $xmlResolver, $xsltBuilder);

        $this->assertSame($sourceString, $signer->buildSourceString());
    }
}
