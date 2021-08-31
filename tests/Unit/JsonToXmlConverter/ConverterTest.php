<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Unit\JsonToXmlConverter;

use Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter\Converter;
use Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter\ConverterInterface;
use Dufrei\ApiJsonCfdiBridge\JsonToXmlConverter\JsonToXmlConvertException;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;

final class ConverterTest extends TestCase
{
    public function testConverterImplementsConverterInterface(): void
    {
        $this->assertInstanceOf(ConverterInterface::class, new Converter());
    }

    public function testConvertSimpleStructure(): void
    {
        $json = <<<JSON
            {
                "root": {
                    "_attributes": {"id": 1, "class": "main"},
                    "foo": {
                        "_attributes": {"id": 2},
                        "deep": {
                            "_attributes": {"id": 3}
                        }
                    },
                    "bar": [
                        {"_attributes": {"id": 4}},
                        {"_attributes": {"id": 5}}
                    ]
                }
            }
            JSON;
        $expected = $this->createXmlDocument(
            <<<XML
                <root id="1" class="main">
                  <foo id="2">
                    <deep id="3"/>
                  </foo>
                  <bar id="4"/>
                  <bar id="5"/>
                </root>
                XML
        );

        $converter = new Converter();
        $converted = $this->createXmlDocument($converter->convert($json));
        $this->assertEquals($expected, $converted);
    }

    public function testConvertWithNameSpaces(): void
    {
        $json = <<<JSON
            {
                "r:root": {
                    "_attributes": {"xmlns:r": "uri:root", "id": 1, "class": "main"},
                    "r:foo": {
                        "_attributes": {"id": 2},
                        "d:deep": {
                            "_attributes": {"xmlns:d": "uri:deep", "id": 3}
                        }
                    },
                    "r:bar": [
                        {"_attributes": {"id": 4}},
                        {"_attributes": {"id": 5}}
                    ]
                }
            }
            JSON;
        $expected = $this->createXmlDocument(
            <<<XML
                <r:root id="1" class="main" xmlns:r="uri:root">
                  <r:foo id="2">
                    <d:deep id="3"  xmlns:d="uri:deep"/>
                  </r:foo>
                  <r:bar id="4"/>
                  <r:bar id="5"/>
                </r:root>
                XML
        );

        $converter = new Converter();
        $converted = $this->createXmlDocument($converter->convert($json));
        $this->assertEquals($expected, $converted);
    }

    public function testErrorOnInvalidJson(): void
    {
        $json = 'invalid json';
        $converter = new Converter();
        $this->expectException(JsonToXmlConvertException::class);
        $this->expectExceptionMessage('Unable to parse JSON');
        $converter->convert($json);
    }

    public function testErrorOnNonObject(): void
    {
        $json = '1';
        $converter = new Converter();
        $this->expectException(JsonToXmlConvertException::class);
        $this->expectExceptionMessage('does not contains a collection');
        $converter->convert($json);
    }

    public function testErrorOnArray(): void
    {
        $json = '[]';
        $converter = new Converter();
        $this->expectException(JsonToXmlConvertException::class);
        $this->expectExceptionMessage('does not contains a collection');
        $converter->convert($json);
    }

    public function testErrorOnNonUniqueRoot(): void
    {
        $json = '{ "foo": {}, "bar": {} }';
        $converter = new Converter();
        $this->expectException(JsonToXmlConvertException::class);
        $this->expectExceptionMessage('does not contains a unique root element');
        $converter->convert($json);
    }

    public function testErrorOnEmptyObject(): void
    {
        $json = '{}';
        $converter = new Converter();
        $this->expectException(JsonToXmlConvertException::class);
        $this->expectExceptionMessage('does not contains a unique root element');
        $converter->convert($json);
    }

    public function testErrorOnInvalidNodeName(): void
    {
        $json = '{ "": {} }';
        $converter = new Converter();
        $this->expectException(JsonToXmlConvertException::class);
        $this->expectExceptionMessage('Invalid element name on /<empty-node-name>');
        $converter->convert($json);
    }

    public function testErrorOnInvalidNodeContent(): void
    {
        $json = '{ "root": "" }';
        $converter = new Converter();
        $this->expectException(JsonToXmlConvertException::class);
        $this->expectExceptionMessage('Invalid element content on /root');
        $converter->convert($json);
    }

    public function testErrorOnInvalidNodeContentInvalidAttributes(): void
    {
        $json = '{"root": {"child": {"_attributes": "foo"}}}';
        $converter = new Converter();
        $this->expectException(JsonToXmlConvertException::class);
        $this->expectExceptionMessage('Invalid element content on /root/child/_attributes');
        $converter->convert($json);
    }
}
