<?php

declare(strict_types=1);

namespace Elazar\Structura\Property;

use Elazar\Structura\Value\Name;

interface Named
{
    public Name $name { get; }
}