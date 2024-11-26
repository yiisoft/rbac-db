<?php

declare(strict_types=1);

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M241008204900UpdateItemType implements TransactionalMigrationInterface
{
    private const TABLE_PREFIX = 'yii_rbac_';
    private const ITEMS_TABLE = self::TABLE_PREFIX . 'item';

    public function up(MigrationBuilder $b): void
    {
        $b
            ->getDb()
            ->createCommand()
            ->update(table: self::ITEMS_TABLE, columns: ['type' => 1], condition: ['type' => 'role'])
            ->execute();
        $b
            ->getDb()
            ->createCommand()
            ->update(table: self::ITEMS_TABLE, columns: ['type' => 2], condition: ['type' => 'permission'])
            ->execute();

        if ($b->getDb()->getDriverName() !== 'sqlite') {
            $b->alterColumn(self::ITEMS_TABLE, 'type', $b->smallInteger()->notNull());
        } else {
            $b->execute('PRAGMA foreign_keys=off;');
            $b->dropIndex(self::ITEMS_TABLE, 'idx-' . self::ITEMS_TABLE . '-type');
            $b->renameTable(self::ITEMS_TABLE, self::ITEMS_TABLE . '_old');
            $b->createTable(
                self::ITEMS_TABLE,
                [
                    'name' => 'string(126) NOT NULL PRIMARY KEY',
                    'type' => 'smallint NOT NULL',
                    'description' => 'string(191)',
                    'rule_name' => 'string(64)',
                    'created_at' => 'integer NOT NULL',
                    'updated_at' => 'integer NOT NULL',
                ],
            );
            $b->createIndex(self::ITEMS_TABLE, 'idx-' . self::ITEMS_TABLE . '-type', 'type');
            $newTableName = self::ITEMS_TABLE;
            $oldTableName = self::ITEMS_TABLE . '_old';
            $b->execute(
                "INSERT INTO $newTableName (name, type, description, rule_name, created_at, updated_at)
                SELECT name, type, description, rule_name, created_at, updated_at
                FROM $oldTableName;"
            );
            $b->dropTable($oldTableName);
            $b->execute('PRAGMA foreign_keys=on;');
        }
    }

//    public function down(MigrationBuilder $b): void
//    {
//        $b->alterColumn(self::ITEMS_TABLE, 'type', $b->string(10)->notNull());
//        $b
//            ->getDb()
//            ->createCommand()
//            ->update(table: self::ITEMS_TABLE, columns: ['type' => 'permission'], condition: ['type' => 2])
//            ->execute();
//        $b
//            ->getDb()
//            ->createCommand()
//            ->update(table: self::ITEMS_TABLE, columns: ['type' => 'role'], condition: ['type' => 1])
//            ->execute();
//    }
}
