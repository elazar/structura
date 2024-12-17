<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Doctrine\DBAL\Connection;

use Elazar\Structura\Collection\NonEmptyNamedCollection;

/**
 * @extends NonEmptyNamedCollection<non-empty-string, NamedConnection>
 */
readonly final class NamedConnections extends NonEmptyNamedCollection
{
    public function __construct(
        NamedConnection $namedConnection,
        NamedConnection... $namedConnections,
    ) {
        parent::__construct($namedConnection, ...$namedConnections);
    }

    public static function fromConnections(
        Connection $connection,
        Connection... $connections,
    ): self {
        return new self(
            ...array_map(
                fn(Connection $connection) => new NamedConnection($connection),
                [$connection, ...$connections],
            )
        );
    }

    public function findByName(string $name): ?NamedConnection
    {
        $hasMatchingNameAndDatabaseName = fn(NamedConnection $namedConnection) =>
            $namedConnection->hasMatchingDatabaseName($name)
            && $namedConnection->hasMatchingName($name);
        $hasMatchingDatabaseName = fn(NamedConnection $namedConnection) =>
            $namedConnection->hasMatchingDatabaseName($name);

        return $this->find($hasMatchingNameAndDatabaseName)
            ?? $this->find($hasMatchingDatabaseName);
    }
}
