<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Actions\ConvertJsonToXml;

use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;

class ConvertJsonToXmlResult
{
    public function __construct(private XmlContent $xml)
    {
    }

    public function getXml(): XmlContent
    {
        return $this->xml;
    }
}
