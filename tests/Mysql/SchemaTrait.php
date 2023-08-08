<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mysql;

trait SchemaTrait
{
    protected function checkItemsChildrenTableForeignKeys(): void
    {
        $this->assertCount(2, $this->getDatabase()->getSchema()->getTableForeignKeys(self::ITEMS_CHILDREN_TABLE));
        $this->assertForeignKey(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent'],
            expectedForeignTableName: self::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: 'RESTRICT',
            expectedOnDelete: 'RESTRICT',
        );
        $this->assertForeignKey(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['child'],
            expectedForeignTableName: self::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: 'RESTRICT',
            expectedOnDelete: 'RESTRICT',
        );
    }
}
