<?php

declare(strict_types=1);

namespace Elazar\Structura\Collection;

use Elazar\Structura\Property\Named;

/**
 * @template K of non-empty-string
 * @template V of Named
 * @extends NamedCollection<K, V>
 */
readonly class NonEmptyNamedCollection extends NamedCollection
{
    /**
     * @param V $element
     * @param V... $elements
     */
    public function __construct(
        Named $element,
        Named... $elements,
    ) {
        parent::__construct($element, ...$elements);
    }
}
