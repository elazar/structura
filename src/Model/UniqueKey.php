<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\{
    Model\Columns,
    Value\Name,
};

readonly class UniqueKey extends Key
{
    public function __construct(
        Name $name,
        Columns $columns,
    ) {
        parent::__construct($name, $columns, unique: true);
    }
}
