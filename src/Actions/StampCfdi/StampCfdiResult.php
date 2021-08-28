<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Actions\StampCfdi;

use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;

class StampCfdiResult
{
    public function __construct(private Cfdi $cfdi)
    {
    }

    public function getCfdi(): Cfdi
    {
        return $this->cfdi;
    }
}
