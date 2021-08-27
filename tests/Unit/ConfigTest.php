<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Unit;

use Dufrei\ApiJsonCfdiBridge\Config;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;

final class ConfigTest extends TestCase
{
    public function testConfigValues(): void
    {
        $xmlResolverPath = '/path';
        $xsltBuilderSaxonPath = '/opt/saxonb';
        $finkokUsername = 'user@domain.com';
        $finkokPassword = 'pazzword-1234';
        $finkokOnProduction = false;

        $config = new Config(
            $xmlResolverPath,
            $xsltBuilderSaxonPath,
            $finkokUsername,
            $finkokPassword,
            $finkokOnProduction
        );

        $this->assertSame($xmlResolverPath, $config->getXmlResolverPath());
        $this->assertSame($xsltBuilderSaxonPath, $config->getXsltBuilderSaxonPath());
        $this->assertSame($finkokUsername, $config->getFinkokUsername());
        $this->assertSame($finkokPassword, $config->getFinkokPassword());
        $this->assertSame($finkokOnProduction, $config->isFinkokOnProduction());
    }
}
