<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use InvalidArgumentException;
use Yiisoft\Rbac\Db\DbSchemaManager;

abstract class DbSchemaManagerTest extends TestCase
{
    use SchemaTrait;

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
            [[], 'At least items table or assignments table name must be set.'],
            [
                ['itemsTable' => null, 'itemsChildrenTable' => null, 'assignmentsTable' => null],
                'At least items table or assignments table name must be set.',
            ],
            [['itemsChildrenTable' => null], 'At least items table or assignments table name must be set.'],
            [['itemsTable' => '', 'assignmentsTable' => 'assignments'], 'Items table name can\'t be empty.'],
            [['itemsTable' => 'items', 'assignmentsTable' => ''], 'Assignments table name can\'t be empty.'],
            [['itemsTable' => '', 'assignmentsTable' => ''], 'Items table name can\'t be empty.'],
            [
                ['itemsTable' => 'items', 'itemsChildrenTable' => '', 'assignmentsTable' => 'assignments'],
                'Items children table name can\'t be empty.',
            ],
            [
                ['itemsTable' => '', 'itemsChildrenTable' => '', 'assignmentsTable' => ''],
                'Items table name can\'t be empty.',
            ],
        ];
    }

    /**
     * @dataProvider dataInitWithEmptyTableNames
     */
    public function testInitWithEmptyTableNames(array $tableNameArguments, string $expectedExceptionMessage): void
    {
        $arguments = ['database' => $this->getDatabase()];
        $arguments = array_merge($tableNameArguments, $arguments);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
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
        $schemaManager = $this->createSchemaManager(itemsChildrenTable: $itemsChildrenTable);
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

    public function testCreateItemTables(): void
    {
        $schemaManager = $this->createSchemaManager(assignmentsTable: null);
        $schemaManager->ensureTables();

        $this->checkItemsTable();
        $this->checkItemsChildrenTable();

        $this->assertFalse($schemaManager->hasTable(self::ASSIGNMENTS_TABLE));
    }

    public function testCreateAssignmentsTable(): void
    {
        $schemaManager = $this->createSchemaManager(itemsTable: null, itemsChildrenTable: null);
        $schemaManager->ensureTables();

        $this->checkAssignmentsTable();

        $this->assertFalse($schemaManager->hasTable(self::ITEMS_TABLE));
        $this->assertFalse($schemaManager->hasTable(self::ITEMS_CHILDREN_TABLE));
    }
}
