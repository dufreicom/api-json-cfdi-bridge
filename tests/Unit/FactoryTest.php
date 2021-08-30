<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Unit;

use CfdiUtils\CadenaOrigen\DOMBuilder;
use CfdiUtils\CadenaOrigen\SaxonbCliBuilder;
use CfdiUtils\CadenaOrigen\XsltBuilderInterface;
use CfdiUtils\XmlResolver\XmlResolver;
use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\StampService\FinkokStampService;
use Dufrei\ApiJsonCfdiBridge\StampService\StampServiceInterface;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;

final class FactoryTest extends TestCase
{
    /**
     * @param string $xmlResolverPath
     * @testWith ["/resources"]
     *           [""]
     */
    public function testCreateXmlResolverIsSetUp(string $xmlResolverPath): void
    {
        $factory = Factory::create([
            'XMLRESOLVER_PATH' => $xmlResolverPath,
        ]);
        $xmlResolver = $factory->createXmlResolver();
        $this->assertInstanceOf(XmlResolver::class, $xmlResolver);
        $this->assertSame($xmlResolverPath, $xmlResolver->getLocalPath());
    }

    public function testCreateXsltBuilderReturnsDombuilderIfNoSanxonbIsSet(): void
    {
        $factory = Factory::create([]);
        $xsltBuilder = $factory->createXsltBuilder();
        $this->assertInstanceOf(DOMBuilder::class, $xsltBuilder);
    }

    public function testCreateXsltBuilderReturnsSanxonbIfIsSet(): void
    {
        $factory = Factory::create([
            'SAXONB_PATH' => $pathSaxonB = '/opt/saxonb',
        ]);
        $xsltBuilder = $factory->createXsltBuilder();
        $this->assertInstanceOf(SaxonbCliBuilder::class, $xsltBuilder);
        /** @var SaxonbCliBuilder $xsltBuilder */
        $this->assertSame($pathSaxonB, $xsltBuilder->getExecutablePath());
    }

    public function testCreateStampServiceCreatesWellSetUpFinkokService(): void
    {
        $factory = Factory::create([
            'FINKOK_PRODUCTION' => 'yes',
            'FINKOK_USERNAME' => $username = 'username',
            'FINKOK_PASSWORD' => $password = 'password',
        ]);
        /** @var FinkokStampService $stampService */
        $stampService = $factory->createStampService();
        $this->assertInstanceOf(FinkokStampService::class, $stampService);
        $this->assertSame($username, $stampService->getUsername());
        $this->assertSame($password, $stampService->getPassword());
        $this->assertTrue($stampService->isProduction());
    }

    public function testCreateStampServiceRespectEnvironment(): void
    {
        $factory = Factory::create([
            'FINKOK_PRODUCTION' => 'no',
            'FINKOK_USERNAME' => 'username',
            'FINKOK_PASSWORD' => 'password',
        ]);
        /** @var FinkokStampService $stampService */
        $stampService = $factory->createStampService();
        $this->assertFalse($stampService->isProduction());
    }

    /**
     * @param bool $useProduction
     * @testWith [true]
     *           [false]
     */
    public function testCreateStampServiceForcedEnvironment(bool $useProduction): void
    {
        $factory = Factory::create([
            'FINKOK_PRODUCTION' => $useProduction ? 'true' : 'false',
            'FINKOK_USERNAME' => 'username',
            'FINKOK_PASSWORD' => 'password',
        ]);
        /** @var FinkokStampService $stampService */
        $stampService = $factory->createStampService();
        $this->assertSame($useProduction, $stampService->isProduction());
    }

    public function testCreateSignXmlActionRespectDependences(): void
    {
        $xmlResolver = $this->createMock(XmlResolver::class);
        $xsltBuilder = $this->createMock(XsltBuilderInterface::class);

        $factory = Factory::create([]);
        $action = $factory->createSignXmlAction($xmlResolver, $xsltBuilder);

        $this->assertSame($xmlResolver, $action->getXmlResolver());
        $this->assertSame($xsltBuilder, $action->getXsltBuilder());
    }

    public function testCreateStampXmlActionRespectDependences(): void
    {
        $stampService = $this->createMock(StampServiceInterface::class);

        $factory = Factory::create([]);
        $action = $factory->createStampCfdiAction($stampService);

        $this->assertSame($stampService, $action->getStampService());
    }

    public function testCreateBuildCfdiFromJsonActionRespectDependences(): void
    {
        $xmlResolver = $this->createMock(XmlResolver::class);
        $xsltBuilder = $this->createMock(XsltBuilderInterface::class);
        $stampService = $this->createMock(StampServiceInterface::class);

        $factory = Factory::create([]);
        $action = $factory->createBuildCfdiFromJsonAction($xmlResolver, $xsltBuilder, $stampService);
        $signXmlAction = $action->getSignXmlAction();
        $stampCfdiAction = $action->getStampCfdiAction();

        $this->assertSame($xmlResolver, $signXmlAction->getXmlResolver());
        $this->assertSame($xsltBuilder, $signXmlAction->getXsltBuilder());
        $this->assertSame($stampService, $stampCfdiAction->getStampService());
    }
}
