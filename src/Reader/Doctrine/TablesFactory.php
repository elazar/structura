<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Doctrine\DBAL\Schema\Table as DoctrineTable;

use Elazar\Structura\Model\Tables;

/**
 * @template SchemaManager of NamedSchemaManager
 * @template SchemaManagers of NamedSchemaManagers<SchemaManager>
 */
class TablesFactory
{
    /**
     * @param SchemaManagers $namedSchemaManagers
     * @param ForeignKeysFactory<SchemaManager, SchemaManagers> $foreignKeysFactory
     */
    public function __construct(
        private NamedSchemaManagers $namedSchemaManagers,
        private ColumnsFactory $columnsFactory,
        private KeysFactory $keysFactory,
        private ForeignKeysFactory $foreignKeysFactory,
    ) { }

    public function getTables(NamedConnection $namedConnection): Tables
    {
        $namedSchemaManager = $this->namedSchemaManagers->get($namedConnection);
        $tableFactory = new TableFactory(
            $this->columnsFactory,
            $this->keysFactory,
            $this->foreignKeysFactory,
        );
        return new Tables(
            ...array_map(
                fn(DoctrineTable $doctrineTable) => $tableFactory->getTable(
                    $namedConnection,
                    $doctrineTable,
                ),
                $namedSchemaManager->schemaManager->listTables()
            ),
        );
    }
}
