<?php

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
                'itemName' => 'string(128) NOT NULL',
                'userId' => 'string(128) NOT NULL',
                'createdAt' => 'integer NOT NULL',
                'PRIMARY KEY ([[itemName]], [[userId]])',
            ],
        );
    }
}
