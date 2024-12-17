<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Doctrine\DBAL\Schema\Table as DoctrineTable;

use Elazar\Structura\{
    Model\Table,
    Value\Name,
};

/**
 * @template F of ForeignKeysFactory
 */
class TableFactory
{
    /**
     * @param F $foreignKeysFactory
     */
    public function __construct(
        private ColumnsFactory $columnsFactory,
        private KeysFactory $keysFactory,
        private ForeignKeysFactory $foreignKeysFactory,
    ) { }

    public function getTable(
        NamedConnection $namedConnection,
        DoctrineTable $doctrineTable,
    ): Table {
        $name = $this->getName($doctrineTable);
        $columns = $this->columnsFactory->getColumns($namedConnection, $doctrineTable);
        $keys = $this->keysFactory->getNonPrimaryKeys($doctrineTable, $columns);
        $foreignKeys = $this->foreignKeysFactory->getForeignKeys(
            $namedConnection,
            $doctrineTable,
            $columns,
        );
        $primaryKey = $this->keysFactory->getPrimaryKey($doctrineTable, $columns);
        return new Table($name, $columns, $keys, $foreignKeys, $primaryKey);
    }

    private function getName(DoctrineTable $doctrineTable): Name
    {
        /** @var non-empty-string $doctrineTableName */
        $doctrineTableName = $doctrineTable->getName();
        return new Name($doctrineTableName);
    }
}
