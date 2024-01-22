<?php

declare(strict_types=1);

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M240118192500CreateAssignmentsTable implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    private const TABLE_PREFIX = 'yii_rbac_';
    private const ASSIGNMENTS_TABLE = self::TABLE_PREFIX . 'assignment';

    public function up(MigrationBuilder $b): void
    {
        $this->createAssignmentsTable($b);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable(self::ASSIGNMENTS_TABLE);
    }

    private function createAssignmentsTable(MigrationBuilder $b): void
    {
        $b->createTable(
            self::ASSIGNMENTS_TABLE,
            [
                'item_name' => 'string(128) NOT NULL',
                'user_id' => 'string(128) NOT NULL',
                'created_at' => 'integer NOT NULL',
                'PRIMARY KEY ([[item_name]], [[user_id]])',
            ],
        );
    }
}
