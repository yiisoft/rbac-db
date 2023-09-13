<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Sqlite;

use Yiisoft\Rbac\Db\DbSchemaManager;

trait SchemaTrait
{
    protected function checkItemsChildrenTable(): void
    {
        parent::checkItemsChildrenTable();

        $this->assertCount(
            1,
            $this->getDatabase()->getSchema()->getTableForeignKeys(DbSchemaManager::ITEMS_CHILDREN_TABLE),
        );
        $this->assertForeignKey(
            table: DbSchemaManager::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent', 'child'],
            expectedForeignTableName: DbSchemaManager::ITEMS_TABLE,
            expectedForeignColumnNames: ['name', 'name'],
        );

        $this->assertCount(
            1,
            $this->getDatabase()->getSchema()->getTableIndexes(DbSchemaManager::ITEMS_CHILDREN_TABLE),
        );
        $this->assertIndex(
            table: DbSchemaManager::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent', 'child'],
            expectedIsUnique: true,
            expectedIsPrimary: true,
        );
    }
}
