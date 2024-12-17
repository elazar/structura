<?php

declare(strict_types=1);

namespace Elazar\Structura\Exception;

class NameNotRecognizedException extends \OutOfBoundsException
{
    public function __construct(public readonly string $name)
    {
        parent::__construct("Name not recognized: {$name}");
    }
}
