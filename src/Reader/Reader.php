<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader;

use Elazar\Structura\Model\Databases;

interface Reader
{
    public function getDatabases(): Databases;
}
