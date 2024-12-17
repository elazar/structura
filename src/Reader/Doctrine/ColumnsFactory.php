<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Doctrine\DBAL\{
    Schema\Column as DoctrineColumn,
    Schema\Table as DoctrineTable,
    Types\Type,
};

use Elazar\Structura\{
    Model\Column,
    Model\Columns,
    Reader\Doctrine\NamedConnection,
    Value\Name,
};

class ColumnsFactory
{
    /**
     * @var array<non-empty-string, array<non-empty-string, Columns>>
     */
    private array $cache;

    public function __construct()
    {
        $this->cache = [];
    }

    public function getColumns(
        NamedConnection $namedConnection,
        DoctrineTable $doctrineTable,
    ): Columns {
        /** @var non-empty-string $databaseKey */
        $databaseKey = $namedConnection->name->value;
        $this->cache[$databaseKey] ??= [];
        /** @var non-empty-string $tableKey */
        $tableKey = $doctrineTable->getName();
        return $this->cache[$databaseKey][$tableKey] ??= new Columns(
            ...array_map(
                fn(DoctrineColumn $doctrineColumn) => $this->getColumn($doctrineColumn),
                $doctrineTable->getColumns(),
            ),
        );
    }

    private function getColumn(
        DoctrineColumn $doctrineColumn,
    ): Column {
        return new Column(
            $this->getName($doctrineColumn),
            $this->getType($doctrineColumn),
            $this->getNullable($doctrineColumn),
            $this->getComment($doctrineColumn),
        );
    }

    private function getName(DoctrineColumn $doctrineColumn): Name
    {
        /** @var non-empty-string $doctrineColumnName */
        $doctrineColumnName = $doctrineColumn->getName();
        return new Name($doctrineColumnName);
    }

    private function getType(DoctrineColumn $doctrineColumn): string
    {
        return Type::lookupName($doctrineColumn->getType());
    }

    private function getNullable(DoctrineColumn $doctrineColumn): bool
    {
        return !$doctrineColumn->getNotnull();
    }

    private function getComment(DoctrineColumn $doctrineColumn): ?string
    {
        return $doctrineColumn->getComment() ?: null;
    }
}
