<?php

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M240118192500CreateItemsTables implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    private const TABLE_PREFIX = 'yii_rbac_';
    private const ITEMS_TABLE = self::TABLE_PREFIX . 'item';
    private const ITEMS_CHILDREN_TABLE = self::TABLE_PREFIX . 'item_child';

    public function up(MigrationBuilder $b): void
    {
        $this->createItemsTable($b);
        $this->createItemsChildrenTable($b);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable(self::ITEMS_CHILDREN_TABLE);
        $b->dropTable(self::ITEMS_TABLE);
    }

    private function createItemsTable(MigrationBuilder $b): void
    {
        $b->createTable(
            self::ITEMS_TABLE,
            [
                'name' => 'string(128) NOT NULL PRIMARY KEY',
                'type' => 'string(10) NOT NULL',
                'description' => 'string(191)',
                'ruleName' => 'string(64)',
                'createdAt' => 'integer NOT NULL',
                'updatedAt' => 'integer NOT NULL',
            ],
        );
        $b->createIndex(self::ITEMS_TABLE, 'idx-' . self::ITEMS_TABLE  . '-type', 'type');
    }

    private function createItemsChildrenTable(MigrationBuilder $b): void
    {
        $b->createTable(
            self::ITEMS_CHILDREN_TABLE,
            [
                'parent' => 'string(128) NOT NULL',
                'child' => 'string(128) NOT NULL',
                'PRIMARY KEY ([[parent]], [[child]])',
                'FOREIGN KEY ([[parent]]) REFERENCES {{%' . self::ITEMS_TABLE . '}} ([[name]])',
                'FOREIGN KEY ([[child]]) REFERENCES {{%' . self::ITEMS_TABLE . '}} ([[name]])',
            ],
        );
    }
}
