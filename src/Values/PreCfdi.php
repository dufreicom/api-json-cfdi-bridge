<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values;

class PreCfdi
{
    public function __construct(
        private XmlContent $xml,
        private SourceString $sourceString,
    ) {
    }

    public function getXml(): XmlContent
    {
        return $this->xml;
    }

    public function getSourceString(): SourceString
    {
        return $this->sourceString;
    }
}
