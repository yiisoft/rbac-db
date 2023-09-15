<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mysql;

use Yiisoft\Rbac\Db\DbSchemaManager;

trait SchemaTrait
{
    protected function checkItemsChildrenTable(): void
    {
        parent::checkItemsChildrenTable();

        $row = $this->getDatabase()->createCommand('SELECT VERSION() AS version')->queryOne();
        $version = $row['version'];
        $onAction = str_starts_with($version, '5') ? 'RESTRICT' : 'NO ACTION';

        $this->assertCount(
            2,
            $this->getDatabase()->getSchema()->getTableForeignKeys(DbSchemaManager::ITEMS_CHILDREN_TABLE),
        );
        $this->assertForeignKey(
            table: DbSchemaManager::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent'],
            expectedForeignTableName: DbSchemaManager::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: $onAction,
            expectedOnDelete: $onAction,
        );
        $this->assertForeignKey(
            table: DbSchemaManager::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['child'],
            expectedForeignTableName: DbSchemaManager::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: $onAction,
            expectedOnDelete: $onAction,
        );

        $this->assertCount(
            2,
            $this->getDatabase()->getSchema()->getTableIndexes(DbSchemaManager::ITEMS_CHILDREN_TABLE),
        );
        $this->assertIndex(
            table: DbSchemaManager::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent', 'child'],
            expectedIsUnique: true,
            expectedIsPrimary: true,
        );
        $this->assertIndex(table: DbSchemaManager::ITEMS_CHILDREN_TABLE, expectedColumnNames: ['child']);
    }
}
