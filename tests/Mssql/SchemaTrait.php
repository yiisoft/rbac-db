<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mssql;

trait SchemaTrait
{
    protected function checkItemsChildrenTable(): void
    {
        parent::checkItemsChildrenTable();

        $this->assertCount(2, $this->getDatabase()->getSchema()->getTableForeignKeys(self::ITEMS_CHILDREN_TABLE));
        $this->assertForeignKey(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent'],
            expectedForeignTableName: self::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: 'NOACTION',
            expectedOnDelete: 'NOACTION',
        );
        $this->assertForeignKey(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['child'],
            expectedForeignTableName: self::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: 'NOACTION',
            expectedOnDelete: 'NOACTION',
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
