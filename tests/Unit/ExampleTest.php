<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Unit;

use Dufrei\ApiJsonCfdiBridge\Example;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;

final class ExampleTest extends TestCase
{
    public function testAssertIsworking(): void
    {
        $example = new Example();
        $this->assertInstanceOf(Example::class, $example);
        $this->markTestSkipped('The unit test environment is working');
    }
}
