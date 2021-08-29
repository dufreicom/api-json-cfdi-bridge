<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values\Base;

use JsonSerializable;
use Stringable;

/**
 * @internal
 */
abstract class StringValueObject implements Stringable, JsonSerializable
{
    public function __construct(protected string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
