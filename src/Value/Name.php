<?php

declare(strict_types=1);

namespace Elazar\Structura\Value;

use Elazar\Structura\Exception\EmptyNameException;

readonly class Name implements \Stringable
{
    /**
     * @param non-empty-string $value
     */
    public function __construct(
        public string $value,
    ) {
        if (empty($value)) {
            throw new EmptyNameException();
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
