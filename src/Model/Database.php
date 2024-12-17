<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\{
    Property\Named,
    Value\Name,
};

readonly class Database implements Named
{
    public function __construct(
        public Name $name,
        public Tables $tables,
    ) { }
}
