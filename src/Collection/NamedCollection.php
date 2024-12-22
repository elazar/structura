<?php

declare(strict_types=1);

namespace Elazar\Structura\Collection;

use Elazar\Structura\{
    Exception\DuplicatedNamesException,
    Exception\NameNotRecognizedException,
    Property\Named,
    Value\Name,
};

/**
 * @template K of non-empty-string
 * @template V of Named
 * @extends Collection<K, V>
 */
readonly class NamedCollection extends Collection
{
    /**
     * @var array<K, V>
     */
    private array $elementsByName;

    /**
     * @param V... $elements
     */
    public function __construct(
        Named... $elements,
    ) {
        /** @var K[] */
        $elementNames = array_map(
            fn(Named $element) => $element->name->value,
            $elements,
        );
        $this->elementsByName = array_combine($elementNames, $elements);

        if (count($elements) !== count($this->elementsByName)) {
            $duplicatedNames = array_keys(
                array_filter(
                    array_count_values($elementNames),
                    fn(int $count) => $count > 1,
                )
            );
            throw new DuplicatedNamesException($duplicatedNames);
        }

        parent::__construct(...$elements);

    }

    /**
     * @return \Traversable<K, V>
     */
    public function getIterator(): \Traversable
    {
        /** @var K $name */
        foreach ($this->elementsByName as $name => $element) {
            yield $name => $element;
        }
    }

    /**
     * @return V
     */
    public function get(Named|Name|string $name): object
    {
        $resolved = self::resolve($name);
        return $this->elementsByName[$resolved]
            ?? throw new NameNotRecognizedException($resolved);
    }

    /**
     * @param (Named|Name|string)... $names
     * @return V[]
     */
    public function getMultiple(...$names): array
    {
        return array_map(
            fn(Named|Name|string $name) => $this->get($name),
            $names,
        );
    }

    public function has(Named|Name|string $name): bool
    {
        return isset($this->elementsByName[self::resolve($name)]);
    }

    private static function resolve(Named|Name|string $name): string
    {
        return $name instanceof Named ? (string) $name->name : (string) $name;
    }
}
