<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge;

class Config
{
    public function __construct(
        private string $xmlResolverPath,
        private string $xsltBuilderSaxonPath,
        private string $finkokUsername,
        private string $finkokPassword,
        private bool $finkokOnProduction,
    ) {
    }

    public function getXmlResolverPath(): string
    {
        return $this->xmlResolverPath;
    }

    public function getXsltBuilderSaxonPath(): string
    {
        return $this->xsltBuilderSaxonPath;
    }

    public function getFinkokUsername(): string
    {
        return $this->finkokUsername;
    }

    public function getFinkokPassword(): string
    {
        return $this->finkokPassword;
    }

    public function isFinkokOnProduction(): bool
    {
        return $this->finkokOnProduction;
    }
}
