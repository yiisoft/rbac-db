<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Yiisoft\Rbac\Db\Command\RbacDbInit;
use Yiisoft\Rbac\Db\Tests\Base\TestCase;

abstract class RbacDbInitTest extends TestCase
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
        new RbacDbInit(...$arguments);
    }

    public function dataExecute(): array
    {
        return [
            [self::ITEMS_CHILDREN_TABLE],
            [null],
        ];
    }

    /**
     * @dataProvider dataExecute
     */
    public function testExecute(string|null $itemsChildrenTable): void
    {
        $app = $this->createApplication($itemsChildrenTable);
        $output = new BufferedOutput(decorated: true);
        $app->find('rbac/db/init')->run(new ArrayInput([]), $output);

        $this->checkTables();

        $newLine = PHP_EOL;
        $expectedOutput = "\033[34mChecking existence of `auth_item` table...\033[39m$newLine" .
            "\033[34m`auth_item` table doesn't exist. Creating...\033[39m$newLine" .
            "\033[42m`auth_item` table has been successfully created.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_item_child` table...\033[39m$newLine" .
            "\033[34m`auth_item_child` table doesn't exist. Creating...\033[39m$newLine" .
            "\033[42m`auth_item_child` table has been successfully created.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_assignment` table...\033[39m$newLine" .
            "\033[34m`auth_assignment` table doesn't exist. Creating...\033[39m$newLine" .
            "\033[42m`auth_assignment` table has been successfully created.\033[49m$newLine" .
            "\033[32mDONE\033[39m$newLine";
        $this->assertSame($expectedOutput, $output->fetch());
    }

    public function testExecuteMultiple(): void
    {
        $app = $this->createApplication();
        $app->find('rbac/db/init')->run(new ArrayInput([]), new NullOutput());

        $output = new BufferedOutput(decorated: true);
        $app->find('rbac/db/init')->run(new ArrayInput([]), $output);

        $this->checkTables();

        $newLine = PHP_EOL;
        $expectedOutput = "\033[34mChecking existence of `auth_item` table...\033[39m$newLine" .
            "\033[43m`auth_item` table already exists. Skipped creating.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_item_child` table...\033[39m$newLine" .
            "\033[43m`auth_item_child` table already exists. Skipped creating.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_assignment` table...\033[39m$newLine" .
            "\033[43m`auth_assignment` table already exists. Skipped creating.\033[49m$newLine" .
            "\033[32mDONE\033[39m$newLine";
        $this->assertSame($expectedOutput, $output->fetch());
    }

    public function testExecuteWithForceAndExistingTables(): void
    {
        $app = $this->createApplication();
        $app->find('rbac/db/init')->run(new ArrayInput([]), new NullOutput());

        $output = new BufferedOutput(decorated: true);
        $app->find('rbac/db/init')->run(new ArrayInput(['--force' => true]), $output);

        $this->checkTables();

        $newLine = PHP_EOL;
        $expectedOutput = "\033[34mChecking existence of `auth_item_child` table...\033[39m$newLine" .
            "\033[34m`auth_item_child` table exists. Dropping...\033[39m$newLine" .
            "\033[42m`auth_item_child` table has been successfully dropped.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_assignment` table...\033[39m$newLine" .
            "\033[34m`auth_assignment` table exists. Dropping...\033[39m$newLine" .
            "\033[42m`auth_assignment` table has been successfully dropped.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_item` table...\033[39m$newLine" .
            "\033[34m`auth_item` table exists. Dropping...\033[39m$newLine" .
            "\033[42m`auth_item` table has been successfully dropped.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_item` table...\033[39m$newLine" .
            "\033[34m`auth_item` table doesn't exist. Creating...\033[39m$newLine" .
            "\033[42m`auth_item` table has been successfully created.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_item_child` table...\033[39m$newLine" .
            "\033[34m`auth_item_child` table doesn't exist. Creating...\033[39m$newLine" .
            "\033[42m`auth_item_child` table has been successfully created.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_assignment` table...\033[39m$newLine" .
            "\033[34m`auth_assignment` table doesn't exist. Creating...\033[39m$newLine" .
            "\033[42m`auth_assignment` table has been successfully created.\033[49m$newLine" .
            "\033[32mDONE\033[39m$newLine";
        $this->assertSame($expectedOutput, $output->fetch());
    }

    public function testExecuteWithForceAndNonExistingTables(): void
    {
        $app = $this->createApplication();
        $output = new BufferedOutput(decorated: true);
        $app->find('rbac/db/init')->run(new ArrayInput(['--force' => true]), $output);

        $this->checkTables();

        $newLine = PHP_EOL;
        $expectedOutput = "\033[34mChecking existence of `auth_item_child` table...\033[39m$newLine" .
            "\033[43m`auth_item_child` table doesn't exist. Skipped dropping.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_assignment` table...\033[39m$newLine" .
            "\033[43m`auth_assignment` table doesn't exist. Skipped dropping.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_item` table...\033[39m$newLine" .
            "\033[43m`auth_item` table doesn't exist. Skipped dropping.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_item` table...\033[39m$newLine" .
            "\033[34m`auth_item` table doesn't exist. Creating...\033[39m$newLine" .
            "\033[42m`auth_item` table has been successfully created.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_item_child` table...\033[39m$newLine" .
            "\033[34m`auth_item_child` table doesn't exist. Creating...\033[39m$newLine" .
            "\033[42m`auth_item_child` table has been successfully created.\033[49m$newLine" .
            "\033[34mChecking existence of `auth_assignment` table...\033[39m$newLine" .
            "\033[34m`auth_assignment` table doesn't exist. Creating...\033[39m$newLine" .
            "\033[42m`auth_assignment` table has been successfully created.\033[49m$newLine" .
            "\033[32mDONE\033[39m$newLine";
        $this->assertSame($expectedOutput, $output->fetch());
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
        $table = $database->getSchema()->getTableSchema(self::ITEMS_TABLE);

        $this->assertTrue($table !== null);

        $columns = $table->getColumns();

        $this->assertArrayHasKey('name', $columns);
        $name = $columns['name'];
        $this->assertSame('string', $name->getType());
        $this->assertSame(128, $name->getSize());
        $this->assertFalse($name->isAllowNull());

        $this->assertArrayHasKey('type', $columns);
        $type = $columns['type'];
        $this->assertSame('string', $type->getType());
//        $this->assertEqualsCanonicalizing([Item::TYPE_ROLE, Item::TYPE_PERMISSION], $type->getEnumValues());
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

//        $this->assertCount(1, $table->getIndexes());
//        $index = array_values($table->getIndexes())[0];
//        $this->assertSame(['type'], $index->getColumns());

        $this->assertSame(['name'], $table->getPrimaryKey());
    }

    private function checkAssignmentsTable(): void
    {
        $database = $this->getDatabase();
        $table = $database->getSchema()->getTableSchema(self::ASSIGNMENTS_TABLE);

        $this->assertTrue($table !== null);

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

        $this->assertTrue($table !== null);

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

        $this->assertSame(['parent', 'child'], $table->getPrimaryKey());
        $this->assertEqualsCanonicalizing(
            [['auth_item', 'child' => 'name'], ['auth_item', 'parent' => 'name']],
            array_values($table->getForeignKeys()),
        );
    }
}
