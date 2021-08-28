<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values;

use JetBrains\PhpStorm\Immutable;
use Stringable;

#[Immutable]
final class Uuid implements Stringable
{
    use StringValueContentTrait;
}
