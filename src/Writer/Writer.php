<?php

declare(strict_types=1);

namespace Elazar\Structura\Writer;

use Elazar\Structura\Model\{
    Databases,
    Relationships,
};

interface Writer
{
    public function write(
        Databases $databases,
        Relationships $relationships,
    ): void;
}
