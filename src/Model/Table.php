<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\{
    Property\Named,
    Value\Name,
};

readonly class Table implements Named
{
    public function __construct(
        public Name $name,
        public Columns $columns,
        public Keys $keys,
        public ForeignKeys $foreignKeys,
        public ?PrimaryKey $primaryKey = null,
    ) { }
}
