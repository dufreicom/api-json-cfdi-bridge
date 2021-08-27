<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter;

use RuntimeException;
use Throwable;

class JsonToXmlConvertException extends RuntimeException
{
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
