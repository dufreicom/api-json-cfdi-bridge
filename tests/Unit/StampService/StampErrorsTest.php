<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Unit\StampService;

use Countable;
use Dufrei\ApiJsonCfdiBridge\StampService\StampError;
use Dufrei\ApiJsonCfdiBridge\StampService\StampErrors;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;
use IteratorAggregate;
use JsonSerializable;

final class StampErrorsTest extends TestCase
{
    public function testMessages(): void
    {
        $errorObjects = [
            new StampError('01', 'Invalid foo'),
            new StampError('02', 'Invalid bar'),
        ];
        $expectedMessages = array_map(static fn (StampError $error): string => (string) $error, $errorObjects);
        $errors = new StampErrors(...$errorObjects);
        $this->assertSame($expectedMessages, $errors->messages());
    }

    public function testJson(): void
    {
        $errorObjects = [
            new StampError('01', 'Invalid foo'),
            new StampError('02', 'Invalid bar'),
        ];
        $expectedJson = json_encode($errorObjects) ?: 'null';
        $errors = new StampErrors(...$errorObjects);
        $this->assertInstanceOf(JsonSerializable::class, $errors);
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($errors) ?: '');
    }

    public function testCount(): void
    {
        $errorObjects = [
            new StampError('01', 'Invalid foo'),
            new StampError('02', 'Invalid bar'),
        ];
        $errors = new StampErrors(...$errorObjects);
        $this->assertInstanceOf(Countable::class, $errors);
        $this->assertCount(count($errorObjects), $errors);
    }

    public function testIteratorAggregate(): void
    {
        $errorObjects = [
            new StampError('01', 'Invalid foo'),
            new StampError('02', 'Invalid bar'),
        ];
        $errors = new StampErrors(...$errorObjects);
        $this->assertInstanceOf(IteratorAggregate::class, $errors);
        $this->assertSame($errorObjects, iterator_to_array($errors));
    }
}
