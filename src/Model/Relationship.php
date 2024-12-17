<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

readonly class Relationship
{
    public function __construct(
        public Entity $first,
        public Entity $second,
        public ?string $label = null,
    ) { }
}
