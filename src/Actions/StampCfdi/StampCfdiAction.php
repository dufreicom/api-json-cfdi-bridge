<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Actions\StampCfdi;

use Dufrei\ApiJsonCfdiBridge\StampService\ServiceException;
use Dufrei\ApiJsonCfdiBridge\StampService\StampException;
use Dufrei\ApiJsonCfdiBridge\StampService\StampServiceInterface;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;

class StampCfdiAction
{
    public function __construct(private StampServiceInterface $stampService)
    {
    }

    public function getStampService(): StampServiceInterface
    {
        return $this->stampService;
    }

    /**
     * @throws StampException
     * @throws ServiceException
     */
    public function execute(XmlContent $preCfdi): StampCfdiResult
    {
        $cfdi = $this->stampService->stamp($preCfdi);
        return new StampCfdiResult($cfdi);
    }
}
