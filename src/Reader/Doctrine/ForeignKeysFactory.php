<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Doctrine\DBAL\Schema\{
    ForeignKeyConstraint as DoctrineForeignKey,
    Table as DoctrineTable,
};

use Elazar\Structura\{
    Exception\AmbiguousForeignKeyException,
    Model\Columns,
    Model\ForeignKey,
    Model\ForeignKeys,
    Reader\Doctrine\NamedConnections,
    Value\Name,
};

/**
 * @template SchemaManager of NamedSchemaManager
 * @template SchemaManagers of NamedSchemaManagers<SchemaManager>
 */
class ForeignKeysFactory
{
    /**
     * @param SchemaManagers $namedSchemaManagers
     */
    public function __construct(
        private NamedConnections $namedConnections,
        private NamedSchemaManagers $namedSchemaManagers,
        private ColumnsFactory $columnsFactory,
    ) { }

    public function getForeignKeys(
        NamedConnection $namedConnection,
        DoctrineTable $doctrineTable,
        Columns $columns,
    ): ForeignKeys {
        return new ForeignKeys(
            ...array_map(
                fn(DoctrineForeignKey $doctrineForeignKey) => $this->getForeignKey(
                    $namedConnection,
                    $doctrineForeignKey,
                    $columns,
                ),
                $doctrineTable->getForeignKeys(),
            ),
        );
    }

    private function getForeignKey(
        NamedConnection $namedConnection,
        DoctrineForeignKey $doctrineForeignKey,
        Columns $columns,
    ): ForeignKey {
        $referenceDatabaseName = $this->getReferenceDatabaseName($namedConnection, $doctrineForeignKey);
        return new ForeignKey(
            $this->getName($doctrineForeignKey),
            $this->getColumns($doctrineForeignKey, $columns),
            $referenceDatabaseName,
            $this->getReferenceTableName($doctrineForeignKey),
            $this->getReferenceColumns($referenceDatabaseName->value, $doctrineForeignKey),
        );
    }

    private function getReferenceDatabaseName(
        NamedConnection $namedConnection,
        DoctrineForeignKey $doctrineForeignKey,
    ): Name {
        $foreignTableName = $doctrineForeignKey->getUnqualifiedForeignTableName();
        $hasTable = fn(NamedSchemaManager $namedSchemaManager) =>
            $namedSchemaManager->schemaManager->tableExists($foreignTableName);

        /** @var SchemaManager $namedSchemaManager */
        $namedSchemaManager = $this->namedSchemaManagers->get($namedConnection);
        if ($hasTable($namedSchemaManager)) {
            return $namedConnection->name;
        }

        $namedSchemaManagersContainingTable = iterator_to_array(
            $this->namedSchemaManagers->filter($hasTable),
        );
        if (count($namedSchemaManagersContainingTable) === 1) {
            return reset($namedSchemaManagersContainingTable)->name;
        }

        /**
         * Multiple configured databases have a table and columns matching
         * those in the specified foreign key, preventing its resolution to a
         * single primary key.
         *
         * Doctrine's schema introspection does not appear to include a
         * reference to the database when a foreign key in one database
         * references a primary key in another database, even if that
         * information is provided in the table schema.
         *
         * While this behavior is not explicitly documented, related online
         * discussions frequently say it's unsupported due in part to most
         * database servers not providing native support for it.
         */
        $databaseNames = array_map(
            fn(NamedSchemaManager $namedSchemaManager)
                => $namedSchemaManager->name->value,
            $namedSchemaManagersContainingTable,
        );
        throw new AmbiguousForeignKeyException(
            $databaseNames,
            $foreignTableName,
            $doctrineForeignKey->getForeignColumns(),
        );
    }

    private function getReferenceTableName(DoctrineForeignKey $doctrineForeignKey): Name
    {
        /** @var non-empty-string $referenceTableName */
        $referenceTableName = $doctrineForeignKey->getForeignTableName();
        return new Name($referenceTableName);
    }

    private function getName(DoctrineForeignKey $doctrineForeignKey): Name
    {
        /** @var non-empty-string $doctrineForeignKeyName */
        $doctrineForeignKeyName = $doctrineForeignKey->getName();
        return new Name($doctrineForeignKeyName);
    }

    private function getColumns(
        DoctrineForeignKey $doctrineForeignKey,
        Columns $columns,
    ): Columns {
        return new Columns(
            ...$columns->getMultiple(
                ...$doctrineForeignKey->getLocalColumns(),
            ),
        );
    }

    private function getReferenceColumns(
        string $foreignDatabaseName,
        DoctrineForeignKey $doctrineForeignKey,
    ): Columns {
        $namedConnection = $this->namedConnections->get($foreignDatabaseName);
        $namedSchemaManager = $this->namedSchemaManagers->get($foreignDatabaseName);
        $foreignTableName = $doctrineForeignKey->getForeignTableName();
        $doctrineTable = $namedSchemaManager->schemaManager->introspectTable($foreignTableName);
        $columns = $this->columnsFactory->getColumns($namedConnection, $doctrineTable);
        return new Columns(
            ...$columns->getMultiple(
                ...$doctrineForeignKey->getForeignColumns(),
            ),
        );
    }
}
