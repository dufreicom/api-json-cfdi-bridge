<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge;

class ConfigBuilder
{
    private string $basePath;

    /** @var array<string, mixed> */
    private array $values;

    /** @param array<string, mixed> $environment */
    public function __construct(array $environment)
    {
        $this->basePath = dirname(__DIR__);
        $this->values = $environment;
    }

    public function build(): Config
    {
        return new Config(
            $this->buildPath($this->getValueAsString('XMLRESOLVER_PATH')),
            $this->buildPath($this->getValueAsString('SAXONB_PATH')),
            $this->getValueAsString('FINKOK_USERNAME'),
            $this->getValueAsString('FINKOK_PASSWORD'),
            $this->getValueAsBool('FINKOK_PRODUCTION'),
        );
    }

    private function getValueAsString(string $key): string
    {
        $value = $this->values[$key] ?? '';
        if (! is_string($value)) {
            $value = (is_scalar($value)) ? strval($value) : null;
        }
        return $value ?? '';
    }

    private function buildPath(string $path): string
    {
        if ('' === $path) {
            return '';
        }
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }
        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }

    private function getValueAsBool(string $key): bool
    {
        $value = strtoupper($this->getValueAsString($key));
        return in_array($value, ['1', 'YES', 'ON', 'TRUE'], true);
    }
}
