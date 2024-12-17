<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

readonly class Entity
{
    public function __construct(
        public Key $key,
        public Cardinality $cardinality,
    ) { }
}

