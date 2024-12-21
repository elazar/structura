<?php

declare(strict_types=1);

namespace Elazar\Structura\Tests\Reader\Doctrine;

use Doctrine\DBAL\{
    Platforms\Exception\NotSupported,
    Schema\Schema,
};

use Elazar\Structura\{
    Collection\NonEmptyNamedCollection,
    Exception\AmbiguousForeignKeyException,
    Model\Key,
    Model\PrimaryKey,
    Model\UniqueKey,
    Property\Named,
    Reader\Doctrine\DoctrineReader,
    Reader\Doctrine\ForeignKeysFactory,
    Reader\Doctrine\NamedConnection,
    Reader\Doctrine\NamedConnections,
    Value\Name,
};

use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type Params from \Doctrine\DBAL\DriverManager
 */

readonly class TestDatabase implements Named
{
    public Name $name;

    /**
     * @param non-empty-string $nameString
     * @param Params $params
     */
    public function __construct(
        string $nameString,
        public array $params,
        public Schema $schema,
    ) {
        $this->name = new Name($nameString);
    }
}

/**
 * @extends NonEmptyNamedCollection<non-empty-string, TestDatabase>
 */
readonly class TestDatabases extends NonEmptyNamedCollection
{
    public function __construct(TestDatabase $database, TestDatabase... $databases)
    {
        parent::__construct($database, ...$databases);
    }
}

class DoctrineReaderTest extends TestCase
{
    /**
     * This test case verifies that the reader correctly populates model
     * objects with appropriate metadata for two tables within a single
     * database including column names and types; primary, unique, and
     * non-unique indexes; and foreign key constraints.
     *
     * Since it is possible to perform this test using an in-memory SQLite
     * database, it is implemented to do so if possible for performance reasons
     * and because the pdo_sqlite extension is installed by default.
     */
    public function testGetDatabasesWithOneDatabase(): void
    {
        if (extension_loaded('pdo_sqlite')) {
            $params = [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ];
        } elseif (extension_loaded('pdo_mysql')) {
            $params = $this->getMySqlParams();
        } else {
            $this->markTestSkipped('pdo_sqlite and pdo_mysql extensions not loaded');
        }

        $schema = new Schema();

        $tableOneSchema = $schema->createTable('table_one');
        $tableOneSchema->addColumn('id', 'integer');
        $tableOneSchema->addColumn('unique_indexed', 'string');
        $tableOneSchema->addColumn('non_unique_indexed', 'integer', [
            'notnull' => false,
            'comment' => 'optional',
        ]);
        $tableOneSchema->addColumn('composite_index_one', 'integer');
        $tableOneSchema->addColumn('composite_index_two', 'integer');
        $tableOneSchema->setPrimaryKey(['id']);
        $tableOneSchema->addUniqueIndex(['unique_indexed'], 'unique_index');
        $tableOneSchema->addIndex(['non_unique_indexed'], 'non_unique_index');
        $tableOneSchema->addIndex(['composite_index_one', 'composite_index_two'], 'composite_index');

        $tableTwoSchema = $schema->createTable('table_two');
        $tableTwoSchema->addColumn('table_one_id', 'integer');
        $tableTwoSchema->addForeignKeyConstraint('table_one', ['table_one_id'], ['id'], name: 'table_one_fk');

        $testDatabases = new TestDatabases(
            new TestDatabase('database_one', $params, $schema),
        );
        $namedConnections = $this->getNamedConnections($testDatabases);

        $doctrineReader = new DoctrineReader($namedConnections);
        $databases = $doctrineReader->getDatabases();

        // Check database
        $this->assertCount(1, $databases);
        $this->assertTrue($databases->has('database_one'));
        $database = $databases->get('database_one');
        $this->assertSame('database_one', $database->name->value);

        // Check tables
        $tables = $database->tables;
        $this->assertCount(2, $tables);

        // Check table table_one
        $this->assertTrue($tables->has('table_one'));
        $tableOne = $tables->get('table_one');
        $this->assertSame('table_one', $tableOne->name->value);
        $this->assertCount(5, $tableOne->columns);

        // Check table_one column id
        $this->assertTrue($tableOne->columns->has('id'));
        $tableOneIdColumn = $tableOne->columns->get('id');
        $this->assertSame('integer', $tableOneIdColumn->type);
        $this->assertFalse($tableOneIdColumn->nullable);
        $this->assertNull($tableOneIdColumn->comment);

        // Check table_one primary key
        $this->assertInstanceOf(PrimaryKey::class, $tableOne->primaryKey);
        // The name of the primary key that is referenced here can't be
        // changed, even if one is as the second parameter in the call to
        // \Doctrine\DBAL\Schema\Table->setPrimaryKey() above, due to how
        // Doctrine (and presumably the database servers it supports) handle
        // names for primary keys.
        // @see \Doctrine\DBAL\Schema\AbstractSchemaManager::_getPortableTableIndexesList()
        $this->assertSame('primary', $tableOne->primaryKey->name->value);
        $this->assertCount(1, $tableOne->primaryKey->columns);
        $this->assertTrue($tableOne->primaryKey->columns->has('id'));
        $this->assertSame($tableOneIdColumn, $tableOne->primaryKey->columns->get('id'));
        $this->assertTrue($tableOne->primaryKey->unique);

        // Check table_one column unique_indexed
        $this->assertTrue($tableOne->columns->has('unique_indexed'));
        $tableOneUniqueColumn = $tableOne->columns->get('unique_indexed');
        $this->assertSame('string', $tableOneUniqueColumn->type);
        $this->assertFalse($tableOneUniqueColumn->nullable);
        $this->assertNull($tableOneUniqueColumn->comment);

        // Check table_one unique index
        $this->assertTrue($tableOne->keys->has('unique_index'));
        $tableOneUniqueKey = $tableOne->keys->get('unique_index');
        $this->assertInstanceOf(UniqueKey::class, $tableOneUniqueKey);
        $this->assertSame('unique_index', $tableOneUniqueKey->name->value);
        $this->assertCount(1, $tableOneUniqueKey->columns);
        $this->assertTrue($tableOneUniqueKey->columns->has('unique_indexed'));
        $this->assertSame($tableOneUniqueColumn, $tableOneUniqueKey->columns->get('unique_indexed'));
        $this->assertTrue($tableOneUniqueKey->unique);

        // Check table_one column non_unique_indexed
        $this->assertTrue($tableOne->columns->has('non_unique_indexed'));
        $tableOneIndexedColumn = $tableOne->columns->get('non_unique_indexed');
        $this->assertSame('integer', $tableOneIndexedColumn->type);
        $this->assertTrue($tableOneIndexedColumn->nullable);
        $this->assertSame('optional', $tableOneIndexedColumn->comment);

        // Check table_one non-unique index
        $this->assertTrue($tableOne->keys->has('non_unique_index'));
        $tableOneIndexedKey = $tableOne->keys->get('non_unique_index');
        $this->assertInstanceOf(Key::class, $tableOneIndexedKey);
        $this->assertSame('non_unique_index', $tableOneIndexedKey->name->value);
        $this->assertCount(1, $tableOneIndexedKey->columns);
        $this->assertTrue($tableOneIndexedKey->columns->has('non_unique_indexed'));
        $this->assertSame($tableOneIndexedColumn, $tableOneIndexedKey->columns->get('non_unique_indexed'));
        $this->assertFalse($tableOneIndexedKey->unique);

        // Check table_one columns composite_index_one and composite_index_two
        $this->assertTrue($tableOne->columns->has('composite_index_one'));
        $tableOneCompositeColumnOne = $tableOne->columns->get('composite_index_one');
        $this->assertTrue($tableOne->columns->has('composite_index_two'));
        $tableOneCompositeColumnTwo = $tableOne->columns->get('composite_index_two');

        // Check table_one composite index
        $this->assertTrue($tableOne->keys->has('composite_index'));
        $tableOneCompositeKey = $tableOne->keys->get('composite_index');
        $this->assertInstanceOf(Key::class, $tableOneCompositeKey);
        $this->assertSame('composite_index', $tableOneCompositeKey->name->value);
        $this->assertCount(2, $tableOneCompositeKey->columns);
        $this->assertTrue($tableOneCompositeKey->columns->has('composite_index_one'));
        $this->assertSame($tableOneCompositeColumnOne, $tableOneCompositeKey->columns->get('composite_index_one'));
        $this->assertTrue($tableOneCompositeKey->columns->has('composite_index_two'));
        $this->assertSame($tableOneCompositeColumnTwo, $tableOneCompositeKey->columns->get('composite_index_two'));
        $this->assertFalse($tableOneCompositeKey->unique);

        // Check table table_two
        $this->assertTrue($tables->has('table_two'));
        $tableTwo = $tables->get('table_two');
        $this->assertSame('table_two', $tableTwo->name->value);
        $this->assertCount(1, $tableTwo->columns);

        // Check table_two column table_one_id
        $this->assertTrue($tableTwo->columns->has('table_one_id'));
        $tableTwoTableOneIdColumn = $tableTwo->columns->get('table_one_id');
        $this->assertSame('integer', $tableTwoTableOneIdColumn->type);
        $this->assertFalse($tableTwoTableOneIdColumn->nullable);
        $this->assertNull($tableTwoTableOneIdColumn->comment);

        // Check table_two foreign key
        $this->assertTrue($tableTwo->foreignKeys->has('table_one_fk'));
        $tableTwoTableOneForeignKey = $tableTwo->foreignKeys->get('table_one_fk');
        $this->assertSame('table_one_fk', $tableTwoTableOneForeignKey->name->value);
        $this->assertCount(1, $tableTwoTableOneForeignKey->columns);
        $this->assertTrue($tableTwoTableOneForeignKey->columns->has('table_one_id'));
        $this->assertSame($tableTwoTableOneIdColumn, $tableTwoTableOneForeignKey->columns->get('table_one_id'));
        $this->assertSame('database_one', $tableTwoTableOneForeignKey->referenceDatabaseName->value);
        $this->assertSame('table_one', $tableTwoTableOneForeignKey->referenceTableName->value);
        $this->assertCount(1, $tableTwoTableOneForeignKey->referenceColumns);
        $this->assertTrue($tableTwoTableOneForeignKey->referenceColumns->has('id'));
        $this->assertSame($tableOneIdColumn, $tableTwoTableOneForeignKey->referenceColumns->get('id'));

        $this->dropDatabases($namedConnections);
    }

    /**
     * This test case verifies that the reader correctly populates model
     * objects for a use case involving two databases wherein each contains a
     * single table and one of the tables has a foreign key constraint that
     * references the other table.
     *
     * In terms of databases supported by Doctrine, this test case only
     * applies to SQL Server and to MySQL when using the InnoDB engine. The
     * latter is used here because it is easier to run in a local Docker
     * environment.
     */
    public function testGetDatabasesWithTwoDatabases(): void
    {
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo_mysql extension not loaded');
        }

        $databaseOneSchema = new Schema();
        $tableOneSchema = $databaseOneSchema->createTable('table_one');
        $tableOneSchema->addColumn('id', 'integer');
        $tableOneSchema->setPrimaryKey(['id']);

        $databaseTwoSchema = new Schema();
        $tableTwoSchema = $databaseTwoSchema->createTable('table_two');
        $tableTwoSchema->addColumn('table_one_id', 'integer');
        $tableTwoSchema->addForeignKeyConstraint(
            'database_one.table_one',
            ['table_one_id'],
            ['id'],
            name: 'table_one_fk',
        );

        $params = $this->getMySqlParams();
        $testDatabases = new TestDatabases(
            new TestDatabase('database_one', $params, $databaseOneSchema),
            new TestDatabase('database_two', $params, $databaseTwoSchema),
        );
        $namedConnections = $this->getNamedConnections($testDatabases);

        $doctrineReader = new DoctrineReader($namedConnections);
        $databases = $doctrineReader->getDatabases();

        // Check databases
        $this->assertCount(2, $databases);

        // Check database database_one
        $this->assertTrue($databases->has('database_one'));
        $databaseOne = $databases->get('database_one');
        $this->assertSame('database_one', $databaseOne->name->value);

        // Check tables
        $tables = $databaseOne->tables;
        $this->assertCount(1, $tables);

        // Check table table_one
        $this->assertTrue($tables->has('table_one'));
        $tableOne = $tables->get('table_one');
        $this->assertSame('table_one', $tableOne->name->value);
        $this->assertCount(1, $tableOne->columns);

        // Check table_one column id
        $this->assertTrue($tableOne->columns->has('id'));
        $tableOneIdColumn = $tableOne->columns->get('id');
        $this->assertSame('integer', $tableOneIdColumn->type);
        $this->assertFalse($tableOneIdColumn->nullable);
        $this->assertNull($tableOneIdColumn->comment);

        // Check table_one primary key
        $primaryKey = $tableOne->primaryKey;
        $this->assertInstanceOf(PrimaryKey::class, $primaryKey);
        $primaryKeyColumns = $primaryKey->columns;
        $this->assertCount(1, $primaryKeyColumns);
        $this->assertTrue($primaryKeyColumns->has('id'));
        $this->assertSame($tableOneIdColumn, $primaryKeyColumns->get('id'));

        // Check database database_two
        $this->assertTrue($databases->has('database_two'));
        $databaseTwo = $databases->get('database_two');
        $this->assertSame('database_two', $databaseTwo->name->value);

        // Check tables
        $tables = $databaseTwo->tables;
        $this->assertCount(1, $tables);

        // Check table table_two
        $this->assertTrue($tables->has('table_two'));
        $tableTwo = $tables->get('table_two');
        $this->assertSame('table_two', $tableTwo->name->value);
        $this->assertCount(1, $tableTwo->columns);

        // Check table_two column table_one_id
        $this->assertTrue($tableTwo->columns->has('table_one_id'));

        // Check table_two foreign key constraints
        $this->assertCount(1, $tableTwo->foreignKeys);
        $tableTwoTableOneForeignKey = $tableTwo->foreignKeys->get('table_one_fk');
        $this->assertSame('table_one_fk', $tableTwoTableOneForeignKey->name->value);
        $this->assertCount(1, $tableTwoTableOneForeignKey->columns);
        $this->assertTrue($tableTwoTableOneForeignKey->columns->has('table_one_id'));
        $this->assertSame('database_one', $tableTwoTableOneForeignKey->referenceDatabaseName->value);
        $this->assertSame('table_one', $tableTwoTableOneForeignKey->referenceTableName->value);
        $this->assertCount(1, $tableTwoTableOneForeignKey->referenceColumns);
        $this->assertTrue($tableTwoTableOneForeignKey->referenceColumns->has('id'));

        $this->dropDatabases($namedConnections);
    }

    #[Covers(ForeignKeysFactory::class, 'getReferenceDatabaseName')]
    public function testGetDatabasesWithAmbiguousForeignKeys(): void
    {
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo_mysql extension not loaded');
        }

        $databasePrimaryKeySchema = new Schema();
        $tablePrimaryKeySchema = $databasePrimaryKeySchema->createTable('table_pk');
        $tablePrimaryKeySchema->addColumn('pk_1', 'integer');
        $tablePrimaryKeySchema->addColumn('pk_2', 'integer');
        $tablePrimaryKeySchema->setPrimaryKey(['pk_1', 'pk_2']);

        $databaseForeignKeySchema = new Schema();
        $tableForeignKeySchema = $databaseForeignKeySchema->createTable('table_fk');
        $tableForeignKeySchema->addColumn('table_pk_1', 'integer');
        $tableForeignKeySchema->addColumn('table_pk_2', 'integer');
        $tableForeignKeySchema->addForeignKeyConstraint(
            'database_pk_1.table_pk',
            ['table_pk_1', 'table_pk_2'],
            ['pk_1', 'pk_2'],
            name: 'table_pk_fk',
        );

        $params = $this->getMySqlParams();
        $testDatabases = new TestDatabases(
            new TestDatabase('database_pk_1', $params, $databasePrimaryKeySchema),
            new TestDatabase('database_pk_2', $params, $databasePrimaryKeySchema),
            new TestDatabase('database_fk', $params, $databaseForeignKeySchema),
        );
        $namedConnections = $this->getNamedConnections($testDatabases);

        $doctrineReader = new DoctrineReader($namedConnections);

        try {
            $databases = $doctrineReader->getDatabases();
            $this->fail('Expected exception was not thrown');
        } catch (AmbiguousForeignKeyException $e) {
            $this->assertEqualsCanonicalizing(
                ['database_pk_1', 'database_pk_2'],
                $e->databaseNames,
            );
            $this->assertSame('table_pk', $e->tableName);
            $this->assertEqualsCanonicalizing(
                ['pk_1', 'pk_2'],
                $e->columnNames,
            );
        }

        $this->dropDatabases($namedConnections);
    }

    /**
     * @return NamedConnections
     */
    private function getNamedConnections(TestDatabases $testDatabases): NamedConnections
    {
        // Create named connections
        $namedConnections = new NamedConnections(
            ...$testDatabases->map(
                fn(TestDatabase $testDatabase) => NamedConnection::fromConfiguration(
                    $testDatabase->params,
                    name: $testDatabase->name->value,
                ),
            ),
        );

        // Drop any databases from previous test runs
        $this->dropDatabases($namedConnections);

        // Create and select databases if needed and supported
        foreach ($namedConnections as $namedConnection) {
            try {
                $name = $namedConnection->name->value;
                $connection = $namedConnection->connection;
                $connection->createSchemaManager()->createDatabase($name);
                $connection->executeStatement("USE {$name}");
            } catch (NotSupported) { }
        }

        // Create tables in databases
        foreach ($testDatabases as $testDatabase) {
            $connection = $namedConnections->get($testDatabase->name)->connection;
            $platform = $connection->getDatabasePlatform();
            foreach ($testDatabase->schema->toSql($platform) as $sql) {
                $connection->executeStatement($sql);
            }
        }

        return $namedConnections;
    }

    private function dropDatabases(NamedConnections $namedConnections): void
    {
        // Reverse the list to handle databases created later referencing
        // databases created earlier so that constraints aren't violated when
        // dropping them
        $reversedNamedConnections = array_reverse(iterator_to_array($namedConnections));
        foreach ($reversedNamedConnections as $namedConnection) {
            try {
                $database = $namedConnection->name->value;
                $schemaManager = $namedConnection->connection->createSchemaManager();
                if (in_array($database, $schemaManager->listDatabases())) {
                    $schemaManager->dropDatabase($database);
                }
            } catch (NotSupported) { }
        }
    }

    private function getMySqlParams(): array
    {
        return [
            'driver' => 'pdo_mysql',
            'host' => 'mysql',
            'user' => 'root',
            'password' => '',
            'driverOptions' => [
                'default_table_options' => [
                    'engine' => 'InnoDB',
                ],
            ],
        ];
    }
}
