<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Actions\BuildCfdiFromJson;

use Dufrei\ApiJsonCfdiBridge\Actions\ConvertJsonToXml\ConvertJsonToXmlAction;
use Dufrei\ApiJsonCfdiBridge\Actions\SignXml\SignXmlAction;
use Dufrei\ApiJsonCfdiBridge\Actions\StampCfdi\StampCfdiAction;
use Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter\JsonToXmlConvertException;
use Dufrei\ApiJsonCfdiBridge\PreCfdiSigner\UnableToSignXmlException;
use Dufrei\ApiJsonCfdiBridge\StampService\ServiceException;
use Dufrei\ApiJsonCfdiBridge\StampService\StampException;
use Dufrei\ApiJsonCfdiBridge\Values\Csd;
use Dufrei\ApiJsonCfdiBridge\Values\JsonContent;

class BuildCfdiFromJsonAction
{
    public function __construct(
        private ConvertJsonToXmlAction $convertJsonToXmlAction,
        private SignXmlAction $signXmlAction,
        private StampCfdiAction $stampCfdiAction,
    ) {
    }

    public function getSignXmlAction(): SignXmlAction
    {
        return $this->signXmlAction;
    }

    public function getStampCfdiAction(): StampCfdiAction
    {
        return $this->stampCfdiAction;
    }

    /**
     * @throws UnableToSignXmlException
     * @throws JsonToXmlConvertException
     * @throws StampException
     * @throws ServiceException
     */
    public function execute(JsonContent $json, Csd $csd): CreateCfdiFromJsonResult
    {
        $convertResult = $this->convertJsonToXmlAction->execute($json);
        $preCfdiResult = $this->signXmlAction->execute($convertResult->getXml(), $csd);
        $cfdiResult = $this->stampCfdiAction->execute($preCfdiResult->getPreCfdi()->getXml());
        return new CreateCfdiFromJsonResult(
            $json,
            $convertResult->getXml(),
            $preCfdiResult->getPreCfdi(),
            $cfdiResult->getCfdi(),
        );
    }
}
