<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Doctrine\DBAL\{
    Connection,
    Configuration,
    DriverManager,
    Platforms\SQLitePlatform,
    Tools\DsnParser,
};

use Elazar\Structura\{
    Exception\NoDatabaseSelectedException,
    Property\Named,
    Value\Name,
};

/**
 * @phpstan-import-type Params from DriverManager
 */
readonly final class NamedConnection implements Named
{
    public Name $name;

    /**
     * @param Name|non-empty-string|null $name
     */
    public function __construct(
        public Connection $connection,
        Name|string|null $name = null,
    ) {
        $this->name = match (true) {
            $name instanceof Name => $name,
            is_string($name) && !empty($name) => new Name($name),
            default => new Name(static::getNameFromConnection($connection)),
        };
    }

    /**
     * @param string|Params $params
     * @param non-empty-string|null $name
     */
    public static function fromConfiguration(
        string|array $params,
        ?Configuration $configuration = null,
        ?string $name = null,
    ): self {
        $parsedParams = is_string($params)
            ? (new DsnParser)->parse($params)
            : $params;
        $connection = DriverManager::getConnection($parsedParams, $configuration);
        return new self($connection, $name);
    }

    /**
     * @return non-empty-string
     */
    private static function getNameFromConnection(Connection $connection): string
    {
        $database = $connection->getDatabase();
        $platform = $connection->getDriver()->getDatabasePlatform($connection);
        if ($platform instanceof SQLitePlatform && $database === 'main') {
            $database = '';
        }
        return match ($database) {
            null    => throw new NoDatabaseSelectedException($connection),
            ''      => '[selected]',
            default => $database,
        };
    }

    public function hasMatchingName(string $name): bool
    {
        return $this->name->value === $name;
    }

    public function hasMatchingDatabaseName(string $databaseName): bool
    {
        return $this->connection->getDatabase() === $databaseName;
    }
}
