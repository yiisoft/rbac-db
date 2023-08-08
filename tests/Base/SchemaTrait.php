<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use PHPUnit\Framework\ExpectationFailedException;
use Yiisoft\Db\Constraint\Constraint;
use Yiisoft\Db\Constraint\ForeignKeyConstraint;
use Yiisoft\Db\Constraint\IndexConstraint;

trait SchemaTrait
{
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
        $this->assertSame(10, $type->getSize());
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

        $primaryKey = $databaseSchema->getTablePrimaryKey(self::ITEMS_TABLE);
        $this->assertInstanceOf(Constraint::class, $primaryKey);
        $this->assertSame(['name'], $primaryKey->getColumnNames());

        $this->assertCount(0, $databaseSchema->getTableForeignKeys(self::ITEMS_TABLE));

        $this->assertCount(2, $databaseSchema->getTableIndexes(self::ITEMS_TABLE));
        $this->assertIndex(
            table: self::ITEMS_TABLE,
            expectedColumnNames: ['name'],
            expectedIsUnique: true,
            expectedIsPrimary: true
        );
        $this->assertIndex(table: self::ITEMS_TABLE, expectedColumnNames: ['type'], expectedName: 'idx-auth_item-type');
    }

    private function checkAssignmentsTable(): void
    {
        $database = $this->getDatabase();
        $databaseSchema = $database->getSchema();
        $table = $databaseSchema->getTableSchema(self::ASSIGNMENTS_TABLE);

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

        $primaryKey = $databaseSchema->getTablePrimaryKey(self::ASSIGNMENTS_TABLE);
        $this->assertInstanceOf(Constraint::class, $primaryKey);
        $this->assertEqualsCanonicalizing(['itemName', 'userId'], $primaryKey->getColumnNames());

        $this->assertCount(0, $databaseSchema->getTableForeignKeys(self::ASSIGNMENTS_TABLE));

        $this->assertCount(1, $databaseSchema->getTableIndexes(self::ASSIGNMENTS_TABLE));
        $this->assertIndex(
            table: self::ASSIGNMENTS_TABLE,
            expectedColumnNames: ['itemName', 'userId'],
            expectedIsUnique: true,
            expectedIsPrimary: true,
        );
    }

    private function checkItemsChildrenTable(): void
    {
        $database = $this->getDatabase();
        $databaseSchema = $database->getSchema();
        $table = $databaseSchema->getTableSchema(self::ITEMS_CHILDREN_TABLE);

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

        $primaryKey = $databaseSchema->getTablePrimaryKey(self::ITEMS_CHILDREN_TABLE);
        $this->assertInstanceOf(Constraint::class, $primaryKey);
        $this->assertEqualsCanonicalizing(['parent', 'child'], $primaryKey->getColumnNames());

        $this->assertCount(1, $databaseSchema->getTableForeignKeys(self::ITEMS_CHILDREN_TABLE));
        var_dump($databaseSchema->getTableForeignKeys(self::ITEMS_CHILDREN_TABLE));
        $this->assertForeignKey(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['child', 'parent'],
            expectedForeignTableName: self::ITEMS_TABLE,
            expectedForeignColumnNames: ['name', 'name'],
        );

        $this->assertCount(1, $databaseSchema->getTableIndexes(self::ITEMS_CHILDREN_TABLE));
        $this->assertIndex(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent', 'child'],
            expectedIsUnique: true,
            expectedIsPrimary: true,
        );
    }

    private function assertForeignKey(
        string $table,
        array $expectedColumnNames,
        string $expectedForeignTableName,
        array $expectedForeignColumnNames,
        ?string $expectedName = null,
        string $expectedOnUpdate = 'NO ACTION',
        string $expectedOnDelete = 'NO ACTION',
    ): void {
        /** @var ForeignKeyConstraint[] $foreignKeys */
        $foreignKeys = $this->getDatabase()->getSchema()->getTableForeignKeys($table);
        $found = false;
        foreach ($foreignKeys as $foreignKey) {
            try {
                $this->assertEqualsCanonicalizing($expectedColumnNames, $foreignKey->getColumnNames());
                $this->assertSame($expectedForeignTableName, $foreignKey->getForeignTableName());
                $this->assertEqualsCanonicalizing($expectedForeignColumnNames, $foreignKey->getForeignColumnNames());
            } catch (ExpectationFailedException) {
                continue;
            }

            $found = true;

            $this->assertSame($expectedOnUpdate, $foreignKey->getOnUpdate());
            $this->assertSame($expectedOnDelete, $foreignKey->getOnDelete());

            if ($expectedName !== null) {
                $this->assertSame($expectedName, $foreignKey->getName());
            }
        }

        if (!$found) {
            self::fail('Foreign key not found.');
        }
    }

    private function assertIndex(
        string $table,
        array $expectedColumnNames,
        ?string $expectedName = null,
        bool $expectedIsUnique = false,
        bool $expectedIsPrimary = false,
    ): void {
        /** @var IndexConstraint[] $indexes */
        $indexes = $this->getDatabase()->getSchema()->getTableIndexes($table);
        $found = false;
        foreach ($indexes as $index) {
            try {
                $this->assertEqualsCanonicalizing($expectedColumnNames, $index->getColumnNames());
            } catch (ExpectationFailedException) {
                continue;
            }

            $found = true;

            $this->assertSame($expectedIsUnique, $index->isUnique());
            $this->assertSame($expectedIsPrimary, $index->isPrimary());

            if ($expectedName !== null) {
                $this->assertSame($expectedName, $index->getName());
            }
        }

        if (!$found) {
            self::fail('Index not found.');
        }
    }

    private function checkNoTables(): void
    {
        $schemaManager = $this->createSchemaManager();

        $this->assertFalse($schemaManager->hasTable($schemaManager->getItemsTable()));
        $this->assertFalse($schemaManager->hasTable($schemaManager->getAssignmentsTable()));
        $this->assertFalse($schemaManager->hasTable($schemaManager->getItemsChildrenTable()));
    }
}
