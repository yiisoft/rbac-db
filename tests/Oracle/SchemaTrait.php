<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Oracle;

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
            expectedOnUpdate: [null, 'NO ACTION'],
            expectedOnDelete: [null, 'NO ACTION'],
        );
        $this->assertForeignKey(
            table: DbSchemaManager::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['child'],
            expectedForeignTableName: DbSchemaManager::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: [null, 'NO ACTION'],
            expectedOnDelete: [null, 'NO ACTION'],
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
