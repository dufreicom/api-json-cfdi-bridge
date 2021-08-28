<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests;

use Closure;
use DOMDocument;
use Dufrei\ApiJsonCfdiBridge\Values\CredentialCsd;
use Dufrei\ApiJsonCfdiBridge\Values\Csd;
use LogicException;
use PhpCfdi\Credentials\Credential;
use Throwable;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function filePath(string $filename): string
    {
        return __DIR__ . '/_files/' . $filename;
    }

    public static function fileContents(string $filename): string
    {
        return file_get_contents(static::filePath($filename)) ?: '';
    }

    protected function createXmlDocument(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;
        $document->loadXML($xml);
        return $document;
    }

    public function createCsdForTesting(): Csd
    {
        $credential = $this->createCredentialForTesting();
        return new CredentialCsd($credential);
    }

    public function createCredentialForTesting(): Credential
    {
        return Credential::openFiles(
            $this->filePath('fake-csd/EKU9003173C9.cer'),
            $this->filePath('fake-csd/EKU9003173C9.key'),
            trim($this->fileContents('fake-csd/EKU9003173C9-password.txt')),
        );
    }

    protected function catchException(Closure $test, string $exceptionToCatch, string $fail = ''): Throwable
    {
        try {
            call_user_func($test);
        } catch (Throwable $exception) {
            if ($exception instanceof $exceptionToCatch) {
                return $exception;
            }
        }
        throw new LogicException($fail ?: "Unable to catch the exception $exceptionToCatch");
    }
}
