<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Values;

use PhpCfdi\Credentials\Credential;
use Stringable;

class CredentialCsd implements Csd
{
    public function __construct(private Credential $credential)
    {
    }

    public function getRfc(): string
    {
        return $this->credential->certificate()->rfc();
    }

    public function getCertificateContents(): string
    {
        return $this->credential->certificate()->pemAsOneLine();
    }

    public function getKeyContents(): string
    {
        $privateKey = $this->credential->privateKey() -> pem();
        return implode('', preg_grep('/^((?!-).)*$/', explode(PHP_EOL, $privateKey)) ?: []);
    }

    public function getCertificateNumber(): string
    {
        return $this->credential->certificate()->serialNumber()->bytes();
    }

    public function sign(Stringable|string $sourceString): string
    {
        return base64_encode($this->credential->sign((string) $sourceString, OPENSSL_ALGO_SHA256));
    }

    public function isCsd(): bool
    {
        return $this->credential->isCsd();
    }

    public function isValid(): bool
    {
        return $this->credential->certificate()->validOn();
    }
}
