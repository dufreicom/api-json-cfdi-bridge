<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class Cfdi
{
    public function __construct(
        private Uuid $uuid,
        private XmlContent $xml,
    ) {
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getXml(): XmlContent
    {
        return $this->xml;
    }
}
