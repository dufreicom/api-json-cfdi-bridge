<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Unit\StampService;

use Dufrei\ApiJsonCfdiBridge\StampService\StampError;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;
use JsonSerializable;
use Stringable;

final class StampErrorTest extends TestCase
{
    public function testStampErrorProperties(): void
    {
        $code = '01';
        $message = 'Invalid foo';
        $error = new StampError($code, $message);
        $this->assertSame($code, $error->getCode());
        $this->assertSame($message, $error->getMessage());
    }

    public function testStampErrorStringable(): void
    {
        $code = '01';
        $message = 'Invalid foo';
        $error = new StampError($code, $message);
        $this->assertInstanceOf(Stringable::class, $error);
        $this->assertSame("[$code] $message", (string) $error);
    }

    public function testStampErrorJsonSerializable(): void
    {
        $code = '01';
        $message = 'Invalid foo';
        $expected = json_encode([
            'code' => $code,
            'message' => $message,
        ]) ?: 'null';
        $error = new StampError($code, $message);
        $this->assertInstanceOf(JsonSerializable::class, $error);
        $this->assertJsonStringEqualsJsonString($expected, json_encode($error) ?: '');
    }
}
