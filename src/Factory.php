<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge;

use CfdiUtils\CadenaOrigen\DOMBuilder;
use CfdiUtils\CadenaOrigen\SaxonbCliBuilder;
use CfdiUtils\CadenaOrigen\XsltBuilderInterface;
use CfdiUtils\XmlResolver\XmlResolver;
use Dufrei\ApiJsonCfdiBridge\Actions\BuildCfdiFromJson\BuildCfdiFromJsonAction;
use Dufrei\ApiJsonCfdiBridge\Actions\ConvertJsonToXml\ConvertJsonToXmlAction;
use Dufrei\ApiJsonCfdiBridge\Actions\SignXml\SignXmlAction;
use Dufrei\ApiJsonCfdiBridge\Actions\StampCfdi\StampCfdiAction;
use Dufrei\ApiJsonCfdiBridge\StampService\FinkokStampService;
use Dufrei\ApiJsonCfdiBridge\StampService\StampServiceInterface;
use PhpCfdi\Finkok\FinkokEnvironment;
use PhpCfdi\Finkok\FinkokSettings;
use PhpCfdi\Finkok\QuickFinkok;
use PhpCfdi\Finkok\Helpers\CancelSigner;
use PhpCfdi\Finkok\Services\Cancel\CancelSignatureCommand;
use PhpCfdi\Finkok\Services\Cancel\CancelSignatureResult;
use PhpCfdi\XmlCancelacion\Models\CancelDocument;
use PhpCfdi\XmlCancelacion\Models\CancelDocuments;
use PhpCfdi\Credentials\Credential;
use PhpCfdi\Finkok\Finkok;

class Factory
{
    final public function __construct(
        private Config $config,
    ) {
    }

    /**
     * @param array<string, mixed>|null $environment
     * @return static
     */
    public static function create(array $environment = null): self
    {
        $config = (new ConfigBuilder($environment ?? $_ENV))->build();
        return new static($config);
    }

    public function createXmlResolver(): XmlResolver
    {
        return new XmlResolver(
            $this->config->getXmlResolverPath(),
        );
    }

    public function createXsltBuilder(): XsltBuilderInterface
    {
        $xsltBuilderSaxonPath = $this->config->getXsltBuilderSaxonPath();
        if ('' !== $xsltBuilderSaxonPath) {
            return new SaxonbCliBuilder($xsltBuilderSaxonPath);
        }
        return new DOMBuilder();
    }

    /**
     * Creates the Finkok service
     *
     * @param bool|null $asProduction NULL to use config, TRUE to force production, FALSE to force development
     * @return StampServiceInterface
     */
    public function createStampService(bool $asProduction = null): StampServiceInterface
    {
        $asProduction ??= $this->config->isFinkokOnProduction();
        $environment = $asProduction ? FinkokEnvironment::makeProduction() : FinkokEnvironment::makeDevelopment();
        $settings = new FinkokSettings(
            $this->config->getFinkokUsername(),
            $this->config->getFinkokPassword(),
            $environment,
        );
        $quickFinkok = new QuickFinkok($settings);
        return new FinkokStampService($quickFinkok);
    }

    public function createSignXmlAction(
        ?XmlResolver $xmlResolver = null,
        ?XsltBuilderInterface $xsltBuilder = null,
    ): SignXmlAction {
        $xmlResolver ??= $this->createXmlResolver();
        $xsltBuilder ??= $this->createXsltBuilder();
        return new SignXmlAction($xmlResolver, $xsltBuilder);
    }

    public function createStampCfdiAction(
        ?StampServiceInterface $stampService = null,
    ): StampCfdiAction {
        return new StampCfdiAction($stampService ?? $this->createStampService());
    }

    public function createBuildCfdiFromJsonAction(
        ?XmlResolver $xmlResolver = null,
        ?XsltBuilderInterface $xsltBuilder = null,
        ?StampServiceInterface $stampService = null,
    ): BuildCfdiFromJsonAction {
        $stampService ??= $this->createStampService();
        return new BuildCfdiFromJsonAction(
            new ConvertJsonToXmlAction(),
            $this->createSignXmlAction($xmlResolver, $xsltBuilder),
            $this->createStampCfdiAction($stampService),
        );
    }

    public function cancelCfdis($inputs): CancelSignatureResult
    {
        $cfdi = $inputs['cfdi'];
        $finkokUsername = $inputs['finkok-username'];
        $finkokPassword = $inputs['finkok-password'];
        $finkokProduction = $inputs['finkok-production'] == 'yes' ? true : false;

        $cancelHelper = new CancelSigner(
            new CancelDocuments(CancelDocument::newWithErrorsUnrelated($cfdi))
        );
        $credential = Credential::create($inputs['certificate'], $inputs['privatekey'], $inputs['passphrase'],$finkokProduction);
        $cancelXml = $cancelHelper->sign($credential);
        
        $finkok = new Finkok(new FinkokSettings($finkokUsername, $finkokPassword));
        $result = $finkok->cancelSignature(new CancelSignatureCommand($cancelXml));
        return $result;
    }
}
