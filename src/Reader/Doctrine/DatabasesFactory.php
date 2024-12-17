<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Elazar\Structura\{
    Model\Database,
    Model\Databases,
};

/**
 * @template T of TablesFactory
 */
class DatabasesFactory
{
    /**
     * @param T $tablesFactory
     */
    public function __construct(
        private NamedConnections $namedConnections,
        private TablesFactory $tablesFactory,
    ) { }

    public function getDatabases(): Databases
    {
        /** @var iterable<non-empty-string, Database> */
        $databases = $this->namedConnections->map(
            fn(NamedConnection $namedConnection) => new Database(
                $namedConnection->name,
                $this->tablesFactory->getTables($namedConnection),
            ),
        );
        return new Databases(...$databases);
    }
}
