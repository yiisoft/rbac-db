<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Sqlite;

trait SchemaTrait
{
    protected function checkItemsChildrenTable(): void
    {
        parent::checkItemsChildrenTable();

        $this->assertCount(1, $this->getDatabase()->getSchema()->getTableForeignKeys('yii_rbac_item_child'));
        $this->assertForeignKey(
            table: 'yii_rbac_item_child',
            expectedColumnNames: ['parent', 'child'],
            expectedForeignTableName: 'yii_rbac_item',
            expectedForeignColumnNames: ['name', 'name'],
        );

        $this->assertCount(1, $this->getDatabase()->getSchema()->getTableIndexes('yii_rbac_item_child'));
        $this->assertIndex(
            table: 'yii_rbac_item_child',
            expectedColumnNames: ['parent', 'child'],
            expectedIsUnique: true,
            expectedIsPrimary: true,
        );
    }
}
