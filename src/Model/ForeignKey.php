<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\Value\Name;

readonly class ForeignKey extends Key
{
    public function __construct(
        public Name $name,
        public Columns $columns,
        public Name $referenceDatabaseName,
        public Name $referenceTableName,
        public Columns $referenceColumns,
    ) { }
}
