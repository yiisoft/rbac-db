<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Sqlite;

trait SchemaTrait
{
    protected function checkItemsChildrenTableForeignKeys(): void
    {
        $this->assertCount(1, $this->getDatabase()->getSchema()->getTableForeignKeys(self::ITEMS_CHILDREN_TABLE));
        $this->assertForeignKey(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent', 'child'],
            expectedForeignTableName: self::ITEMS_TABLE,
            expectedForeignColumnNames: ['name', 'name'],
        );
    }
}
