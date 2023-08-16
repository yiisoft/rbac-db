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

        $this->assertCount(2, $this->getDatabase()->getSchema()->getTableForeignKeys(self::ITEMS_CHILDREN_TABLE));
        $this->assertForeignKey(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent'],
            expectedForeignTableName: self::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: $onAction,
            expectedOnDelete: $onAction,
        );
        $this->assertForeignKey(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['child'],
            expectedForeignTableName: self::ITEMS_TABLE,
            expectedForeignColumnNames: ['name'],
            expectedOnUpdate: $onAction,
            expectedOnDelete: $onAction,
        );

        $this->assertCount(2, $this->getDatabase()->getSchema()->getTableIndexes(self::ITEMS_CHILDREN_TABLE));
        $this->assertIndex(
            table: self::ITEMS_CHILDREN_TABLE,
            expectedColumnNames: ['parent', 'child'],
            expectedIsUnique: true,
            expectedIsPrimary: true,
        );
        $this->assertIndex(table: self::ITEMS_CHILDREN_TABLE, expectedColumnNames: ['child']);
    }
}
