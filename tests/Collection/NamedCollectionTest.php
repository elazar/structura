<?php

declare(strict_types=1);

namespace Elazar\Structura\Tests\Collection;

use Elazar\Structura\{
    Collection\NamedCollection,
    Exception\DuplicatedNamesException,
    Property\Named,
    Value\Name,
};

use PHPUnit\Framework\TestCase;

class NamedCollectionTest extends TestCase
{
    public function testConstructWithDuplicatedNames(): void
    {
        try {
            new NamedCollection(
                ...array_fill(0, 2, new class implements Named {
                    public function __construct(
                        public readonly Name $name = new Name('foo'),
                    ) { }
                })
            );
            $this->fail('Expected exception not thrown');
        } catch (DuplicatedNamesException $e) {
            $this->assertSame(['foo'], $e->duplicatedNames);
        }
    }
}
