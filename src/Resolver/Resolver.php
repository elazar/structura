<?php

declare(strict_types=1);

namespace Elazar\Structura\Resolver;

use Elazar\Structura\Model\{
    Databases,
    Relationships,
};

interface Resolver
{
    public function resolve(Databases $databases): Relationships;
}
