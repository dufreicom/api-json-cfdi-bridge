<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Actions\ConvertJsonToXml;

use Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter\Converter as SimpleJsonToXmlConverter;
use Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter\ConverterInterface as SimpleJsonToXmlConverterInterface;
use Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter\JsonToXmlConvertException;
use Dufrei\ApiJsonCfdiBridge\Values\JsonContent;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;

class ConvertJsonToXmlAction
{
    private SimpleJsonToXmlConverterInterface $converter;

    public function __construct(SimpleJsonToXmlConverterInterface $converter = null)
    {
        $this->converter = $converter ?? new SimpleJsonToXmlConverter();
    }

    /**
     * @throws JsonToXmlConvertException
     */
    public function execute(JsonContent $json): ConvertJsonToXmlResult
    {
        $contents = $this->converter->convert($json);
        return new ConvertJsonToXmlResult(
            new XmlContent($contents),
        );
    }
}
