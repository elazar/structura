<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\Collection\NamedCollection;

/**
 * @extends NamedCollection<non-empty-string, ForeignKey>
 */
readonly class ForeignKeys extends NamedCollection
{
    public function __construct(
        ForeignKey... $foreignKeys,
    ) {
        parent::__construct(...$foreignKeys);
    }
}
