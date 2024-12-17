<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\{
    Property\Named,
    Value\Name,
};

readonly class Column implements Named
{
    public function __construct(
        public Name $name,
        public string $type,
        public bool $nullable,
        public ?string $comment = null,
    ) { }
}
