<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\StampService;

use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;

interface StampServiceInterface
{
    /**
     * @param XmlContent $preCfdi
     * @return Cfdi
     * @throws StampException
     */
    public function stamp(XmlContent $preCfdi): Cfdi;
}
