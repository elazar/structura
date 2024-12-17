<?php

declare(strict_types=1);

namespace Elazar\Structura\Exception;

class AmbiguousForeignKeyException extends \RuntimeException
{
    /**
     * @param string[] $databaseNames
     * @param string $tableName
     * @param string[] $columnNames
     */
    public function __construct(
        public readonly array $databaseNames,
        public readonly string $tableName,
        public readonly array $columnNames,
    ) {
        parent::__construct(
            'Ambiguous foreign key found on columns '
            . join(', ', $columnNames) . ' from table '
            . $tableName . ' in databases '
            . join(', ', $databaseNames),
        );
    }
}
