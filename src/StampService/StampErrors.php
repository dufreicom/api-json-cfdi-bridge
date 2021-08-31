<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\StampService;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements IteratorAggregate<StampError>
 */
class StampErrors implements IteratorAggregate, Countable, JsonSerializable
{
    /** @var StampError[] */
    private array $errors;

    private int $count;

    public function __construct(StampError ...$errors)
    {
        $this->errors = $errors;
        $this->count = count($errors);
    }

    /** @return Traversable<StampError> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->errors);
    }

    /** @return string[] */
    public function messages(): array
    {
        $messages = [];
        foreach ($this->errors as $error) {
            $messages[] = (string) $error;
        }
        return $messages;
    }

    public function count(): int
    {
        return $this->count;
    }

    /** @return StampError[] */
    public function jsonSerialize(): array
    {
        return $this->errors;
    }
}
