<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter;

use Stringable;

interface ConverterInterface
{
    public function convert(Stringable|string $json): string;
}
