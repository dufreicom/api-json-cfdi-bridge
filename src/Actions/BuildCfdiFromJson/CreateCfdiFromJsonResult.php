<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Actions\BuildCfdiFromJson;

use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\JsonContent;
use Dufrei\ApiJsonCfdiBridge\Values\PreCfdi;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;

class CreateCfdiFromJsonResult
{
    public function __construct(
        private JsonContent $json,
        private XmlContent $convertedXml,
        private PreCfdi $preCfdi,
        private Cfdi $cfdi,
    ) {
    }

    public function getJson(): JsonContent
    {
        return $this->json;
    }

    public function getConvertedXml(): XmlContent
    {
        return $this->convertedXml;
    }

    public function getPreCfdi(): PreCfdi
    {
        return $this->preCfdi;
    }

    public function getCfdi(): Cfdi
    {
        return $this->cfdi;
    }
}
