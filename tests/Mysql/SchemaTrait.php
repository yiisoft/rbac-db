<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mysql;

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
            $this->getDatabase()->getSchema()->getTableForeignKeys(self::$itemsChildrenTable),
        );
        $this->assertForeignKey(
            table: self::$itemsChildrenTable,
            expectedColumnNames: ['parent'],
            expectedForeignTableName: self::$itemsTable,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: $onAction,
            expectedOnDelete: $onAction,
        );
        $this->assertForeignKey(
            table: self::$itemsChildrenTable,
            expectedColumnNames: ['child'],
            expectedForeignTableName: self::$itemsTable,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: $onAction,
            expectedOnDelete: $onAction,
        );

        $this->assertCount(
            2,
            $this->getDatabase()->getSchema()->getTableIndexes(self::$itemsChildrenTable),
        );
        $this->assertIndex(
            table: self::$itemsChildrenTable,
            expectedColumnNames: ['parent', 'child'],
            expectedIsUnique: true,
            expectedIsPrimary: true,
        );
        $this->assertIndex(table: self::$itemsChildrenTable, expectedColumnNames: ['child']);
    }
}
