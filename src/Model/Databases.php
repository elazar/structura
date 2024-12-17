<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\Collection\NonEmptyNamedCollection;

/**
 * @extends NonEmptyNamedCollection<non-empty-string, Database>
 */
readonly class Databases extends NonEmptyNamedCollection
{
    public function __construct(
        Database $database,
        Database... $databases,
    ) {
        parent::__construct($database, ...$databases);
    }
}
