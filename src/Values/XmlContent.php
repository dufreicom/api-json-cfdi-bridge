<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values;

use DOMDocument;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class XmlContent extends Base\StringValueObject
{
    public function toDocument(): DOMDocument
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;
        $document->loadXML($this->value);
        return $document;
    }
}
