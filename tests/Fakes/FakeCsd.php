<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Fakes;

use Dufrei\ApiJsonCfdiBridge\Values\Csd;
use LogicException;
use Stringable;

class FakeCsd implements Csd
{
    public function __construct(
        private string $rfc,
        private string $certificateContents,
        private string $certificateNumber,
        private bool $certificateIsCsd,
        private bool $certificateIsValid,
        private ?string $predefinedSignature = null,
    ) {
    }

    public function getRfc(): string
    {
        return $this->rfc;
    }

    public function getCertificateContents(): string
    {
        return $this->certificateContents;
    }

    public function getCertificateNumber(): string
    {
        return $this->certificateNumber;
    }

    public function isCsd(): bool
    {
        return $this->certificateIsCsd;
    }

    public function isValid(): bool
    {
        return $this->certificateIsValid;
    }

    public function sign(Stringable|string $sourceString): string
    {
        if (null === $this->predefinedSignature) {
            throw new LogicException('To call FakeCsd::sign you must provide a predefined signature');
        }

        return $this->predefinedSignature;
    }
}
