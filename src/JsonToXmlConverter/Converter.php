<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter;

use CfdiUtils\Utils\Xml;
use DOMDocument;
use DOMElement;
use JsonException;
use stdClass;
use Stringable;

class Converter implements ConverterInterface
{
    public function convert(Stringable|string $json): string
    {
        $document = $this->convertToDocument($json);
        return $document->saveXML() ?: '';
    }

    /** @throws JsonToXmlConvertException */
    public function convertToDocument(Stringable|string $json): DOMDocument
    {
        try {
            $data = json_decode((string) $json, flags: JSON_THROW_ON_ERROR);
            // @phpstan-ignore-next-line
        } catch (JsonException $exception) {
            throw new JsonToXmlConvertException('Unable to parse JSON', $exception);
        }

        if (! $data instanceof stdClass) {
            throw new JsonToXmlConvertException('The JSON does not contains a collection');
        }

        $keys = get_object_vars($data);
        if (1 !== count($keys)) {
            throw new JsonToXmlConvertException('The JSON does not contains a unique root element');
        }

        $rootKey = (string) array_key_first($keys);

        $document = new DOMDocument('1.0', 'utf-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;
        $this->convertRecursive($document, $document, $rootKey, $data->{$rootKey});
        return $document;
    }

    /** @throws JsonToXmlConvertException */
    private function convertRecursive(
        DOMDocument $document,
        DOMDocument|DOMElement $parent,
        string $elementName,
        mixed $contents,
    ): void {
        // process multiple entries when not un root element
        if (is_array($contents) && $parent instanceof DOMElement) {
            foreach ($contents as $content) {
                $this->convertRecursive($document, $parent, $elementName, $content);
            }
            return;
        }

        // validate name
        if (! $this->isValidTagName($elementName)) {
            $parentPath = $this->buildElementPath($parent, $elementName);
            throw new JsonToXmlConvertException("Invalid element name on $parentPath");
        }

        // validate content
        if (! $contents instanceof stdClass) {
            $parentPath = $this->buildElementPath($parent, $elementName);
            throw new JsonToXmlConvertException("Invalid element content on $parentPath");
        }

        $element = $document->createElement($elementName);
        $parent->appendChild($element);

        foreach (get_object_vars($contents) as $name => $data) {
            if ('_attributes' === $name && $data instanceof stdClass) {
                foreach (get_object_vars($data) as $attributeName => $attributeValue) {
                    if (is_scalar($attributeValue)) {
                        $element->setAttribute($attributeName, (string)$attributeValue);
                    }
                }
                continue;
            }

            $this->convertRecursive($document, $element, $name, $data);
        }
    }

    private function buildElementPath(DOMDocument|DOMElement $parent, string $elementName): string
    {
        return '/' . ltrim(sprintf('%s/%s', $parent->getNodePath() ?? '', $elementName ?: '<empty-node-name>'), '/');
    }

    public function isValidTagName(string $name): bool
    {
        return Xml::isValidXmlName($name);
    }
}
