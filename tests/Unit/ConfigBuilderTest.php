<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Unit;

use Dufrei\ApiJsonCfdiBridge\ConfigBuilder;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;

final class ConfigBuilderTest extends TestCase
{
    public function testBuildWithNoEnvironment(): void
    {
        $builder = new ConfigBuilder([]);
        $config = $builder->build();

        $this->assertSame('', $config->getXmlResolverPath());
        $this->assertSame('', $config->getXsltBuilderSaxonPath());
        $this->assertSame('', $config->getFinkokUsername());
        $this->assertSame('', $config->getFinkokPassword());
        $this->assertFalse($config->isFinkokOnProduction());
    }

    public function testBuildWithData(): void
    {
        $builder = new ConfigBuilder([
            'XMLRESOLVER_PATH' => '/resources',
            'SAXONB_PATH' => '/opt/saxon/saxonb',
            'FINKOK_PRODUCTION' => 1,
            'FINKOK_USERNAME' => 'username',
            'FINKOK_PASSWORD' => 'password',
        ]);
        $config = $builder->build();

        $this->assertSame('/resources', $config->getXmlResolverPath());
        $this->assertSame('/opt/saxon/saxonb', $config->getXsltBuilderSaxonPath());
        $this->assertSame('username', $config->getFinkokUsername());
        $this->assertSame('password', $config->getFinkokPassword());
        $this->assertTrue($config->isFinkokOnProduction());
    }

    public function testXmlResolverPathUsesAbsolute(): void
    {
        $path = '/absolute/path';

        $config = (new ConfigBuilder([
            'XMLRESOLVER_PATH' => $path,
        ]))->build();

        $this->assertSame($path, $config->getXmlResolverPath());
    }

    public function testXmlResolverPathUsesRelative(): void
    {
        $path = 'relative/path';
        $expected = dirname($this->filePath(''), 2) . '/' . $path;

        $config = (new ConfigBuilder([
            'XMLRESOLVER_PATH' => $path,
        ]))->build();

        $this->assertSame($expected, $config->getXmlResolverPath());
    }

    public function testXsltBuilderSaxonPathUsesAbsolute(): void
    {
        $path = '/absolute/path';

        $config = (new ConfigBuilder([
            'SAXONB_PATH' => $path,
        ]))->build();

        $this->assertSame($path, $config->getXsltBuilderSaxonPath());
    }

    public function testXsltBuilderSaxonPathUsesRelative(): void
    {
        $path = 'relative/path';
        $expected = dirname($this->filePath(''), 2) . '/' . $path;

        $config = (new ConfigBuilder([
            'SAXONB_PATH' => $path,
        ]))->build();

        $this->assertSame($expected, $config->getXsltBuilderSaxonPath());
    }
}
