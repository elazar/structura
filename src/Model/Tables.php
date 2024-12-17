<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\Collection\NonEmptyNamedCollection;

/**
 * @extends NonEmptyNamedCollection<non-empty-string, Table>
 */
readonly class Tables extends NonEmptyNamedCollection
{
    public function __construct(
        Table $table,
        Table... $tables,
    ) {
        parent::__construct($table, ...$tables);
    }
}
