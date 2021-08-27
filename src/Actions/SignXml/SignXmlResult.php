<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Actions\SignXml;

use Dufrei\ApiJsonCfdiBridge\Values\PreCfdi;

class SignXmlResult
{
    public function __construct(private PreCfdi $preCfdi)
    {
    }

    public function getPreCfdi(): PreCfdi
    {
        return $this->preCfdi;
    }
}
