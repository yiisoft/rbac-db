<?php

declare(strict_types=1);

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M240118192500CreateItemsTables implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    private const ITEMS_TABLE = '{{%yii_rbac_item}}';
    private const ITEMS_CHILDREN_TABLE = '{{%yii_rbac_item_child}}';

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
                'name' => 'string(126) NOT NULL PRIMARY KEY',
                'type' => 'string(10) NOT NULL',
                'description' => 'string(191)',
                'rule_name' => 'string(64)',
                'created_at' => 'integer NOT NULL',
                'updated_at' => 'integer NOT NULL',
            ],
        );
        $b->createIndex(
            table: self::ITEMS_TABLE,
            name: 'idx-' . $b->getDb()->getQuoter()->getRawTableName(self::ITEMS_TABLE) . '-type',
            columns: 'type',
        );
    }

    private function createItemsChildrenTable(MigrationBuilder $b): void
    {
        $b->createTable(
            self::ITEMS_CHILDREN_TABLE,
            [
                'parent' => 'string(126) NOT NULL',
                'child' => 'string(126) NOT NULL',
                'PRIMARY KEY ([[parent]], [[child]])',
                'FOREIGN KEY ([[parent]]) REFERENCES ' . self::ITEMS_TABLE . ' ([[name]])',
                'FOREIGN KEY ([[child]]) REFERENCES ' . self::ITEMS_TABLE . ' ([[name]])',
            ],
        );
    }
}
