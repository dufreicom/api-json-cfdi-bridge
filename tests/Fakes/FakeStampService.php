<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Fakes;

use Dufrei\ApiJsonCfdiBridge\StampService\StampException;
use Dufrei\ApiJsonCfdiBridge\StampService\StampServiceInterface;
use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;
use OutOfRangeException;

final class FakeStampService implements StampServiceInterface
{
    /**
     * @param array<Cfdi|StampException> $stampQueue
     */
    public function __construct(
        private array $stampQueue,
    ) {
    }

    public function stamp(XmlContent $preCfdi): Cfdi
    {
        return $this->stampQueuePop();
    }

    public function stampQueuePop(): Cfdi
    {
        $element = array_pop($this->stampQueue);
        if (null === $element) {
            throw new OutOfRangeException('Stamp queue is empty');
        }
        if ($element instanceof StampException) {
            throw $element;
        }
        return $element;
    }

    public function stampQueueIsEmpty(): bool
    {
        return ([] === $this->stampQueue);
    }
}
