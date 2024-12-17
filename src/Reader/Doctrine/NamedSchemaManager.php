<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Doctrine\DBAL\{
    Platforms\AbstractPlatform,
    Schema\AbstractSchemaManager,
};

use Elazar\Structura\{
    Property\Named,
    Value\Name,
};

/**
 * @template-covariant Platform of AbstractPlatform
 * @template-covariant SchemaManager of AbstractSchemaManager<Platform>
 */
readonly final class NamedSchemaManager implements Named
{
    /**
     * @param SchemaManager $schemaManager
     */
    public function __construct(
        public AbstractSchemaManager $schemaManager,
        public Name $name,
    ) { }

    /**
     * @return self<Platform, SchemaManager>
     */
    public static function fromNamedConnection(
        NamedConnection $namedConnection,
    ): self {
        /** @var SchemaManager */
        $schemaManager = $namedConnection->connection->createSchemaManager();
        $name = $namedConnection->name;
        /** @var self<Platform, SchemaManager> */
        return new self($schemaManager, $name);
    }
}
