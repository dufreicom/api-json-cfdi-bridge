<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values;

final class Token
{
    public function __construct(private string $token)
    {
    }

    public static function createRandom(): self
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $plainText = sha1(random_bytes(255));
        return new self($plainText);
    }

    public function verify(string $hash): bool
    {
        return password_verify($this->token, $hash);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getHash(): string
    {
        return password_hash($this->token, algo: null);
    }
}
