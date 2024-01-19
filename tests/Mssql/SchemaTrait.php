<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mssql;

trait SchemaTrait
{
    protected function checkItemsChildrenTable(): void
    {
        parent::checkItemsChildrenTable();

        $this->assertCount(
            2,
            $this->getDatabase()->getSchema()->getTableForeignKeys(self::$itemsChildrenTable),
        );
        $this->assertForeignKey(
            table: self::$itemsChildrenTable,
            expectedColumnNames: ['parent'],
            expectedForeignTableName: self::$itemsTable,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: 'NOACTION',
            expectedOnDelete: 'NOACTION',
        );
        $this->assertForeignKey(
            table: self::$itemsChildrenTable,
            expectedColumnNames: ['child'],
            expectedForeignTableName: self::$itemsTable,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: 'NOACTION',
            expectedOnDelete: 'NOACTION',
        );

        $this->assertCount(
            1,
            $this->getDatabase()->getSchema()->getTableIndexes(self::$itemsChildrenTable),
        );
        $this->assertIndex(
            table: self::$itemsChildrenTable,
            expectedColumnNames: ['parent', 'child'],
            expectedIsUnique: true,
            expectedIsPrimary: true,
        );
    }
}
