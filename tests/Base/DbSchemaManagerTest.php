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
        if (str_starts_with($this->getName(), 'testInitWithEmptyTableNames')) {
            return;
        }

        if ($this->getName() === 'testHasTableWithEmptyString' || $this->getName() === 'testDropTableWithEmptyString') {
            return;
        }

        if (str_starts_with($this->getName(), 'testGet')) {
            return;
        }

        parent::tearDown();
    }

    protected function populateDatabase(): void
    {
        // Skip
    }

    public function dataInitWithEmptyTableNames(): array
    {
        return [
            [
                ['itemsTable' => null, 'itemsChildrenTable' => null, 'assignmentsTable' => null],
                'At least items table or assignments table name must be set.',
            ],
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

    public function testCreateTablesSeparately(): void
    {
        $schemaManager = $this->createSchemaManager();
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

        $this->assertFalse($schemaManager->hasTable(DbSchemaManager::ASSIGNMENTS_TABLE));
    }

    public function testCreateAssignmentsTable(): void
    {
        $schemaManager = $this->createSchemaManager(itemsTable: null, itemsChildrenTable: null);
        $schemaManager->ensureTables();

        $this->checkAssignmentsTable();

        $this->assertFalse($schemaManager->hasTable(DbSchemaManager::ITEMS_TABLE));
        $this->assertFalse($schemaManager->hasTable(DbSchemaManager::ITEMS_CHILDREN_TABLE));
    }

    public function testHasTableWithEmptyString(): void
    {
        $schemaManager = $this->createSchemaManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table name must be non-empty.');
        $schemaManager->hasTable('');
    }

    public function testDropTableWithEmptyString(): void
    {
        $schemaManager = $this->createSchemaManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table name must be non-empty.');
        $schemaManager->dropTable('');
    }

    public function testGetItemsTable(): void
    {
        $this->assertSame('yii_rbac_item', $this->createSchemaManager()->getItemsTable());
    }

    public function testGetItemsChildrenTable(): void
    {
        $this->assertSame('yii_rbac_item_child', $this->createSchemaManager()->getItemsChildrenTable());
    }

    public function testGetAssignmentsTable(): void
    {
        $this->assertSame('yii_rbac_assignment', $this->createSchemaManager()->getAssignmentsTable());
    }

    public function testEnsureNoTables(): void
    {
        $schemaManager = $this->createSchemaManager();
        $schemaManager->ensureTables();
        $schemaManager->ensureNoTables();
        $this->checkNoTables();
    }
}
