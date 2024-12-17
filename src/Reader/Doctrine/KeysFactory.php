<?php

declare(strict_types=1);

namespace Elazar\Structura\Reader\Doctrine;

use Doctrine\DBAL\Schema\{
    Index as DoctrineIndex,
    Table as DoctrineTable,
};

use Elazar\Structura\{
    Model\Columns,
    Model\Key,
    Model\Keys,
    Model\PrimaryKey,
    Model\UniqueKey,
    Value\Name,
};

class KeysFactory
{
    public function getPrimaryKey(
        DoctrineTable $doctrineTable,
        Columns $columns,
    ): ?PrimaryKey {
        $doctrinePrimaryKey = $doctrineTable->getPrimaryKey();
        return $doctrinePrimaryKey === null
            ? null
            : new PrimaryKey(
                $this->getKeyName($doctrinePrimaryKey),
                $this->getKeyColumns($doctrinePrimaryKey, $columns),
            );
    }

    public function getNonPrimaryKeys(
        DoctrineTable $doctrineTable,
        Columns $columns,
    ): Keys {
        $doctrineIndexes = array_filter(
            $doctrineTable->getIndexes(),
            fn(DoctrineIndex $doctrineIndex) => !$doctrineIndex->isPrimary(),
        );
        return new Keys(
            ...array_map(
                fn(DoctrineIndex $doctrineIndex) => $this->getKey(
                    $doctrineIndex,
                    $columns,
                ),
                $doctrineIndexes,
            ),
        );
    }

    private function getKey(
        DoctrineIndex $doctrineIndex,
        Columns $columns,
    ): Key {
        $indexName = $this->getKeyName($doctrineIndex);
        $indexColumns = $this->getKeyColumns($doctrineIndex, $columns);
        return $doctrineIndex->isUnique()
            ? new UniqueKey($indexName, $indexColumns)
            : new Key($indexName, $indexColumns);
    }

    private function getKeyName(
        DoctrineIndex $doctrineIndex,
    ): Name {
        /** @var non-empty-string */
        $name = $doctrineIndex->getName();
        return new Name($name);
    }

    private function getKeyColumns(
        DoctrineIndex $doctrineIndex,
        Columns $columns,
    ): Columns {
        return new Columns(
            ...$columns->getMultiple(
                ...$doctrineIndex->getColumns(),
            ),
        );
    }
}
