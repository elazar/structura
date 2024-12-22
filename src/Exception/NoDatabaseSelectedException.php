<?php

declare(strict_types=1);

namespace Elazar\Structura\Exception;

class NoDatabaseSelectedException extends \RuntimeException
{
    /**
     * @param mixed $connection
     */
    public function __construct(public readonly mixed $connection)
    {
        $connectionString = print_r($connection, true);
        parent::__construct("No database selected on connection: {$connectionString}");
    }
}
