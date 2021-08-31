<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Actions\SignXml;

use CfdiUtils\CadenaOrigen\XsltBuilderInterface;
use CfdiUtils\XmlResolver\XmlResolver;
use Dufrei\ApiJsonCfdiBridge\PreCfdiSigner\PreCfdiSigner;
use Dufrei\ApiJsonCfdiBridge\PreCfdiSigner\UnableToSignXmlException;
use Dufrei\ApiJsonCfdiBridge\Values\Csd;
use Dufrei\ApiJsonCfdiBridge\Values\PreCfdi;
use Dufrei\ApiJsonCfdiBridge\Values\SourceString;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;

class SignXmlAction
{
    public function __construct(
        private XmlResolver $xmlResolver,
        private XsltBuilderInterface $xsltBuilder,
    ) {
    }

    public function getXmlResolver(): XmlResolver
    {
        return $this->xmlResolver;
    }

    public function getXsltBuilder(): XsltBuilderInterface
    {
        return $this->xsltBuilder;
    }

    /** @throws UnableToSignXmlException */
    public function execute(XmlContent $xml, Csd $csd): SignXmlResult
    {
        if (! $csd->isCsd()) {
            $message = sprintf('The certificate %s from %s is not a CSD', $csd->getCertificateNumber(), $csd->getRfc());
            throw new UnableToSignXmlException($message);
        }
        if (! $csd->isValid()) {
            $message = sprintf('The certificate %s from %s is expired', $csd->getCertificateNumber(), $csd->getRfc());
            throw new UnableToSignXmlException($message);
        }

        $document = $xml->toDocument();
        $signer = new PreCfdiSigner($document, $this->xmlResolver, $this->xsltBuilder);

        $signer->putIssuerRfc($csd->getRfc());
        $signer->putCertificateValue($csd->getCertificateContents());
        $signer->putCertificateNumber($csd->getCertificateNumber());

        $sourceString = new SourceString($signer->buildSourceString());
        $signature = $csd->sign($sourceString);
        $signer->putSignatureValue($signature);

        return new SignXmlResult(
            new PreCfdi(
                new XmlContent($document->saveXML() ?: ''),
                $sourceString,
            ),
        );
    }
}
