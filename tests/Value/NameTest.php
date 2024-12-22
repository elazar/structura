<?php

declare(strict_types=1);

namespace Elazar\Structura\Tests\Value;

use Elazar\Structura\{
    Exception\EmptyNameException,
    Value\Name,
};

use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testConstructWithEmptyName(): void
    {
        try {
            new Name('');
        } catch (EmptyNameException $e) {
            $this->assertSame('Name cannot be empty', $e->getMessage());
        }
    }
}
