<?php

declare(strict_types=1);

namespace Elazar\Structura\Collection;

/**
 * @template K of array-key
 * @template V of object
 * @implements \IteratorAggregate<K, V>
 */
readonly class Collection implements \IteratorAggregate, \Countable
{
    /**
     * @var array<K, V>
     */
    private array $elements;

    /**
     * @param V... $elements
     */
    public function __construct(
        object... $elements,
    ) {
        $this->elements = array_values($elements);
    }

    /**
     * @return \Traversable<K, V>
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->elements as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @template R of mixed
     * @param callable(V): R $callback
     * @return iterable<K, R>
     */
    public function map(callable $callback): iterable
    {
        foreach ($this->elements as $key => $value) {
            yield $key => $callback($value);
        }
    }

    /**
     * @return \Traversable<K, V>
     */
    public function filter(callable $callback): \Traversable
    {
        foreach ($this->elements as $key => $value) {
            if ($callback($value)) {
                yield $key => $value;
            }
        }
    }

    /**
     * @param callable(V): bool $callback
     * @return V|null
     */
    public function find(callable $callback): mixed
    {
        foreach ($this->elements as $element) {
            if ($callback($element)) {
                return $element;
            }
        }
        return null;
    }

    public function count(): int
    {
        return count($this->elements);
    }
}
