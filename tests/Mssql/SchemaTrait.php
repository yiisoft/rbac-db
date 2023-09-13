<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mssql;

use Yiisoft\Rbac\Db\DbSchemaManager;

trait SchemaTrait
{
    protected function checkItemsChildrenTable(): void
    {
        parent::checkItemsChildrenTable();

        $this->assertCount(
            2,
            $this->getDatabase()->getSchema()->getTableForeignKeys(DbSchemaManager::ITEMS_CHILDREN_TABLE),
        );
        $this->assertForeignKey(
            table: DbSchemaManager::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent'],
            expectedForeignTableName: DbSchemaManager::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: 'NOACTION',
            expectedOnDelete: 'NOACTION',
        );
        $this->assertForeignKey(
            table: DbSchemaManager::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['child'],
            expectedForeignTableName: DbSchemaManager::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: 'NOACTION',
            expectedOnDelete: 'NOACTION',
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
