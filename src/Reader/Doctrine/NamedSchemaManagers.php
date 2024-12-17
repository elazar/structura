<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Elazar\Structura\Collection\NonEmptyNamedCollection;

/**
 * @template SchemaManager of NamedSchemaManager
 * @extends NonEmptyNamedCollection<non-empty-string, SchemaManager>
 */
readonly final class NamedSchemaManagers extends NonEmptyNamedCollection
{
    /**
     * @param SchemaManager $schemaManager
     * @param SchemaManager... $schemaManagers
     */
    public function __construct(
        NamedSchemaManager $schemaManager,
        NamedSchemaManager... $schemaManagers,
    ) {
        parent::__construct($schemaManager, ...$schemaManagers);
    }

    /**
     * @return self<SchemaManager>
     */
    public static function fromNamedConnections(
        NamedConnections $namedConnections
    ): self {
        /** @var self<SchemaManager> */
        return new self(
            ...$namedConnections->map(
                fn(NamedConnection $namedConnection): NamedSchemaManager =>
                    NamedSchemaManager::fromNamedConnection($namedConnection),
            ),
        );
    }
}
