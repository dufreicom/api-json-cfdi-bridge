<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values;

use DOMDocument;
use JetBrains\PhpStorm\Immutable;
use Stringable;

#[Immutable]
final class XmlContent implements Stringable
{
    use StringValueContentTrait;

    public function toDocument(): DOMDocument
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;
        $document->loadXML($this->value);
        return $document;
    }
}
