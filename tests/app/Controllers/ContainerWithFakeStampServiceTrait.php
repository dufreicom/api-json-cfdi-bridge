<?php

declare(strict_types=1);

namespace App\Tests\Controllers;

use Dufrei\ApiJsonCfdiBridge\Factory;
use Dufrei\ApiJsonCfdiBridge\StampService\StampException;
use Dufrei\ApiJsonCfdiBridge\StampService\StampServiceInterface;
use Dufrei\ApiJsonCfdiBridge\Tests\Fakes\FakeFactory;
use Dufrei\ApiJsonCfdiBridge\Tests\Fakes\FakeStampService;
use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;

trait ContainerWithFakeStampServiceTrait
{
    private function setUpContainerWithPedefinedStampServiceResponse(Cfdi|StampException|null $result = null): void
    {
        $stampService = new FakeStampService(array_filter([$result]));
        $this->setUpContainerWithFakeStampService($stampService);
    }

    private function setUpContainerWithFakeStampService(StampServiceInterface $stampService): void
    {
        $factory = FakeFactory::create();
        $factory->setStampService($stampService);
        $this->getContainer()->add(Factory::class, $factory);
    }
}
