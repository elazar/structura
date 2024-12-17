<?php

declare(strict_types=1);

namespace Elazar\Structura\Exception;

class EmptyNameException extends \LengthException
{
    public function __construct()
    {
        parent::__construct('Name cannot be empty');
    }
}
