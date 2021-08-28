<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\StampService;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<StampError>
 */
class StampErrors implements IteratorAggregate
{
    /** @var StampError[] */
    private array $errors;

    public function __construct(StampError ...$errors)
    {
        $this->errors = $errors;
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
}
