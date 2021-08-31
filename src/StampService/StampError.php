<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\StampService;

use JsonSerializable;
use Stringable;

class StampError implements Stringable, JsonSerializable
{
    public function __construct(
        private string $code,
        private string $message,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function __toString(): string
    {
        return "[$this->code] $this->message";
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
