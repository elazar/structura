<?php

declare(strict_types=1);

namespace Elazar\Structura\Collection;

/**
 * @template K of array-key
 * @template V of object
 * @extends Collection<K, V>
 */
readonly class NonEmptyCollection extends Collection
{
    /**
     * @param V $element
     * @param V... $elements
     */
    public function __construct(
        object $element,
        object... $elements,
    ) {
        parent::__construct($element, ...$elements);
    }
}
