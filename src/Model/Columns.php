<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\Collection\NonEmptyNamedCollection;

/**
 * @extends NonEmptyNamedCollection<non-empty-string, Column>
 */
readonly class Columns extends NonEmptyNamedCollection
{
    public function __construct(
        Column $column,
        Column... $columns,
    ) {
        parent::__construct($column, ...$columns);
    }
}
