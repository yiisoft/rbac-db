<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use InvalidArgumentException;
use Yiisoft\Db\Constraint\IndexConstraint;
use Yiisoft\Rbac\Db\DbSchemaManager;

abstract class SchemaManagerTest extends TestCase
{
    protected function setUp(): void
    {
        // Skip
    }

    protected function tearDown(): void
    {
        if (!str_starts_with($this->getName(), 'testInitWithEmptyTableNames')) {
            parent::tearDown();
        }
    }

    protected function populateDatabase(): void
    {
        // Skip
    }

    public function dataInitWithEmptyTableNames(): array
    {
        return [
            [['itemsTable' => '', 'assignmentsTable' => 'assignments'], 'Items'],
            [['itemsTable' => 'items', 'assignmentsTable' => ''], 'Assignments'],
            [['itemsTable' => '', 'assignmentsTable' => ''], 'Items'],
            [
                ['itemsTable' => 'items', 'assignmentsTable' => 'assignments', 'itemsChildrenTable' => ''],
                'Items children',
            ],
            [['itemsTable' => '', 'assignmentsTable' => '', 'itemsChildrenTable' => ''], 'Items'],
        ];
    }

    /**
     * @dataProvider dataInitWithEmptyTableNames
     */
    public function testInitWithEmptyTableNames(array $tableNameArguments, $expectedWrongTableName): void
    {
        $arguments = ['database' => $this->getDatabase()];
        $arguments = array_merge($tableNameArguments, $arguments);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("$expectedWrongTableName table name can't be empty.");
        new DbSchemaManager(...$arguments);
    }

    public function dataCreateTablesSeparately(): array
    {
        return [
            [self::ITEMS_CHILDREN_TABLE],
            [null],
        ];
    }

    /**
     * @dataProvider dataCreateTablesSeparately
     */
    public function testCreateTablesSeparately(string|null $itemsChildrenTable): void
    {
        $schemaManager = $this->createSchemaManager($itemsChildrenTable);
        $schemaManager->createItemsTable();
        $schemaManager->createItemsChildrenTable();
        $schemaManager->createAssignmentsTable();

        $this->checkTables();
    }

    public function testEnsureTablesMultiple(): void
    {
        $schemaManager = $this->createSchemaManager();
        $schemaManager->ensureTables();
        $schemaManager->ensureTables();

        $this->checkTables();
    }

    private function checkTables(): void
    {
        $this->checkItemsTable();
        $this->checkAssignmentsTable();
        $this->checkItemsChildrenTable();
    }

    private function checkItemsTable(): void
    {
        $database = $this->getDatabase();
        $databaseSchema = $database->getSchema();
        $table = $databaseSchema->getTableSchema(self::ITEMS_TABLE);

        $schemaManager = $this->createSchemaManager();
        $this->assertTrue($schemaManager->hasTable($schemaManager->getItemsTable()));

        $columns = $table->getColumns();

        $this->assertArrayHasKey('name', $columns);
        $name = $columns['name'];
        $this->assertSame('string', $name->getType());
        $this->assertSame(128, $name->getSize());
        $this->assertFalse($name->isAllowNull());

        $this->assertArrayHasKey('type', $columns);
        $type = $columns['type'];
        $this->assertSame('string', $type->getType());
        $this->assertFalse($type->isAllowNull());

        $this->assertArrayHasKey('description', $columns);
        $description = $columns['description'];
        $this->assertSame('string', $description->getType());
        $this->assertSame(191, $description->getSize());
        $this->assertTrue($description->isAllowNull());

        $this->assertArrayHasKey('ruleName', $columns);
        $ruleName = $columns['ruleName'];
        $this->assertSame('string', $ruleName->getType());
        $this->assertSame(64, $ruleName->getSize());
        $this->assertTrue($ruleName->isAllowNull());

        $this->assertArrayHasKey('createdAt', $columns);
        $createdAt = $columns['createdAt'];
        $this->assertSame('integer', $createdAt->getType());
        $this->assertFalse($createdAt->isAllowNull());

        $this->assertArrayHasKey('updatedAt', $columns);
        $updatedAt = $columns['updatedAt'];
        $this->assertSame('integer', $updatedAt->getType());
        $this->assertFalse($updatedAt->isAllowNull());

        /** @var IndexConstraint[] $indexes */
        $indexes = $databaseSchema->getTableIndexes(self::ITEMS_TABLE);
        $this->assertCount(2, $indexes);
        $expectedIndexColumnNames = ['type', 'name'];
        foreach ($indexes as $index) {
            $columnNames = $index->getColumnNames();
            $this->assertCount(1, $columnNames);
            $this->assertContains($columnNames[0], $expectedIndexColumnNames);
        }

        $this->assertSame(['name'], $table->getPrimaryKey());
    }

    private function checkAssignmentsTable(): void
    {
        $database = $this->getDatabase();
        $table = $database->getSchema()->getTableSchema(self::ASSIGNMENTS_TABLE);

        $schemaManager = $this->createSchemaManager();
        $this->assertTrue($schemaManager->hasTable($schemaManager->getAssignmentsTable()));

        $columns = $table->getColumns();

        $this->assertArrayHasKey('itemName', $columns);
        $itemName = $columns['itemName'];
        $this->assertSame('string', $itemName->getType());
        $this->assertSame(128, $itemName->getSize());
        $this->assertFalse($itemName->isAllowNull());

        $this->assertArrayHasKey('userId', $columns);
        $userId = $columns['userId'];
        $this->assertSame('string', $userId->getType());
        $this->assertSame(128, $userId->getSize());
        $this->assertFalse($userId->isAllowNull());

        $this->assertArrayHasKey('createdAt', $columns);
        $createdAt = $columns['createdAt'];
        $this->assertSame('integer', $createdAt->getType());
        $this->assertFalse($createdAt->isAllowNull());

        $this->assertSame(['itemName', 'userId'], $table->getPrimaryKey());
        $this->assertSame([['auth_item', 'itemName' => 'name']], array_values($table->getForeignKeys()));
    }

    private function checkItemsChildrenTable(): void
    {
        $database = $this->getDatabase();
        $table = $database->getSchema()->getTableSchema(self::ITEMS_CHILDREN_TABLE);

        $schemaManager = $this->createSchemaManager();
        $this->assertTrue($schemaManager->hasTable($schemaManager->getItemsChildrenTable()));

        $columns = $table->getColumns();

        $this->assertArrayHasKey('parent', $columns);
        $parent = $columns['parent'];
        $this->assertSame('string', $parent->getType());
        $this->assertSame(128, $parent->getSize());
        $this->assertFalse($parent->isAllowNull());

        $this->assertArrayHasKey('child', $columns);
        $child = $columns['child'];
        $this->assertSame('string', $child->getType());
        $this->assertSame(128, $child->getSize());
        $this->assertFalse($child->isAllowNull());

        $this->assertEqualsCanonicalizing(['parent', 'child'], $table->getPrimaryKey());
        $this->assertEqualsCanonicalizing(
            [['auth_item', 'child' => 'name'], ['auth_item', 'parent' => 'name']],
            array_values($table->getForeignKeys()),
        );
    }
}
