<?php

declare(strict_types=1);

namespace Elazar\Structura\Exception;

class DuplicatedNamesException extends \RuntimeException
{
    /**
     * @param string[] $duplicatedNames
     */
    public function __construct(public readonly array $duplicatedNames)
    {
        parent::__construct('Names are not unique: ' . join(', ', $duplicatedNames));
    }
}
