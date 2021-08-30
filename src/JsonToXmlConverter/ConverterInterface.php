<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter;

use Stringable;

interface ConverterInterface
{
    /**
     * @param Stringable|string $json
     * @return string
     * @throws JsonToXmlConvertException
     */
    public function convert(Stringable|string $json): string;
}
