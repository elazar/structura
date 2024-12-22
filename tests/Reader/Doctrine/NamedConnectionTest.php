<?php

declare(strict_types=1);

namespace Elazar\Structura\Tests\Reader\Doctrine;

use Doctrine\DBAL\Connection;

use Elazar\Structura\{
    Exception\NoDatabaseSelectedException,
    Reader\Doctrine\NamedConnection,
};

use PHPUnit\Framework\TestCase;

class NamedConnectionTest extends TestCase
{
    public function testConstructGetNameFromConnectionWithNonEmptyName(): void
    {
        $name = 'foo';
        $connection = $this->createConfiguredStub(
            Connection::class,
            ['getDatabase' => $name],
        );
        $namedConnection = new NamedConnection($connection);
        $this->assertSame($name, $namedConnection->name->value);
    }

    /**
     * This covers cases where there is no database name, e.g. when using
     * SQLite.
     */
    public function testConstructGetNameFromConnectionWithEmptyName(): void
    {
        $connection = $this->createConfiguredStub(
            Connection::class,
            ['getDatabase' => ''],
        );
        $namedConnection = new NamedConnection($connection);
        $this->assertSame('[selected]', $namedConnection->name->value);
    }

    /**
     * This covers cases where the connection database is null because no
     * database has been selected or otherwise specified.
     */
    public function testConstructGetNameFromConnectionWithNullName(): void
    {
        $connection = $this->createConfiguredStub(
            Connection::class,
            ['getDatabase' => null],
        );
        try {
            new NamedConnection($connection);
            $this->fail('Expected exception not thrown');
        } catch (NoDatabaseSelectedException $e) {
            $this->assertStringStartsWith(
                'No database selected on connection',
                $e->getMessage(),
            );
        }
    }

    public function testFromConfigurationWithDsnWithoutName(): void
    {
        $namedConnection = NamedConnection::fromConfiguration(
            'pdo-sqlite:///:memory:',
        );
        $this->assertSame('[selected]', $namedConnection->name->value);
    }
}
