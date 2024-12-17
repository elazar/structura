<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Elazar\Structura\{
    Model\Databases,
    Reader\Reader,
};

/**
 * This reader requires the doctrine/dbal library.
 *
 * @see https://packagist.org/packages/doctrine/dbal
 * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/4.2/index.html
 *
 * @template SchemaManager of NamedSchemaManager
 * @template SchemaManagers of NamedSchemaManagers<SchemaManager>
 */
class DoctrineReader implements Reader
{
    public function __construct(
        private NamedConnections $namedConnections,
    ) { }

    public function getDatabases(): Databases
    {
        $databasesFactory = new DatabasesFactory(
            $this->namedConnections,
            $this->getTablesFactory(),
        );
        return $databasesFactory->getDatabases();
    }

    /**
     * @return SchemaManagers
     */
    private function getNamedSchemaManagers(): NamedSchemaManagers
    {
        /** @var SchemaManagers */
        return NamedSchemaManagers::fromNamedConnections(
            $this->namedConnections,
        );
    }

    private function getColumnsFactory(): ColumnsFactory
    {
        return new ColumnsFactory();
    }

    /**
     * @param SchemaManagers $namedSchemaManagers
     * @return ForeignKeysFactory<SchemaManager, SchemaManagers>
     */
    private function getForeignKeysFactory(
        NamedSchemaManagers $namedSchemaManagers,
        ColumnsFactory $columnsFactory,
    ): ForeignKeysFactory {
        return new ForeignKeysFactory(
            $this->namedConnections,
            $namedSchemaManagers,
            $columnsFactory,
        );
    }

    /**
     * @return TablesFactory<SchemaManager, SchemaManagers>
     */
    private function getTablesFactory(): TablesFactory
    {
        $namedSchemaManagers = $this->getNamedSchemaManagers();
        $columnsFactory = $this->getColumnsFactory();
        return new TablesFactory(
            $namedSchemaManagers,
            $columnsFactory,
            $this->getKeysFactory(),
            $this->getForeignKeysFactory(
                $namedSchemaManagers,
                $columnsFactory,
            ),
        );
    }

    private function getKeysFactory(): KeysFactory
    {
        return new KeysFactory();
    }
}
