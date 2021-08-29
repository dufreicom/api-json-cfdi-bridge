<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Fakes;

use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\StampService\StampServiceInterface;

class FakeFactory extends Factory
{
    private ?StampServiceInterface $stampService;

    public function setStampService(?StampServiceInterface $stampService): self
    {
        $this->stampService = $stampService;
        return $this;
    }

    public function createStampService(bool $asProduction = null): StampServiceInterface
    {
        if (null !== $this->stampService) {
            return $this->stampService;
        }
        return parent::createStampService($asProduction);
    }
}
