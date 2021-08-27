<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values;

/**
 * Do not use this trait outside this namespace
 * @internal
 */
trait StringValueContentTrait
{
    public function __construct(private string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
