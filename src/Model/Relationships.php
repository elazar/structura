<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\Collection\NonEmptyCollection;

/**
 * @extends NonEmptyCollection<non-empty-string, Relationship>
 */
readonly class Relationships extends NonEmptyCollection
{
    public function __construct(
        Relationship $relationship,
        Relationship... $relationships,
    ) {
        parent::__construct($relationship, ...$relationships);
    }
}
