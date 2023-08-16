<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Sqlite;

trait SchemaTrait
{
    protected function checkItemsChildrenTable(): void
    {
        parent::checkItemsChildrenTable();

        $this->assertCount(1, $this->getDatabase()->getSchema()->getTableForeignKeys(self::ITEMS_CHILDREN_TABLE));
        $this->assertForeignKey(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent', 'child'],
            expectedForeignTableName: self::ITEMS_TABLE,
            expectedForeignColumnNames: ['name', 'name'],
        );

        $this->assertCount(1, $this->getDatabase()->getSchema()->getTableIndexes(self::ITEMS_CHILDREN_TABLE));
        $this->assertIndex(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent', 'child'],
            expectedIsUnique: true,
            expectedIsPrimary: true,
        );
    }
}
