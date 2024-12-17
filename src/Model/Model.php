<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

readonly class Model
{
    public function __construct(
        public Databases $databases,
        public Relationships $relationships,
    ) { }
}
