<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Oracle;

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
            expectedOnUpdate: [null, 'NO ACTION'],
            expectedOnDelete: [null, 'NO ACTION'],
        );
        $this->assertForeignKey(
            table: self::$itemsChildrenTable,
            expectedColumnNames: ['child'],
            expectedForeignTableName: self::$itemsTable,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: [null, 'NO ACTION'],
            expectedOnDelete: [null, 'NO ACTION'],
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
