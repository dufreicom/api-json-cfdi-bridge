<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values;

use Stringable;

interface Csd
{
    public function getRfc(): string;

    public function getCertificateContents(): string;

    public function getCertificateNumber(): string;

    public function sign(Stringable|string $sourceString): string;

    public function isCsd(): bool;

    public function isValid(): bool;
}
