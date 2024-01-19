<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

abstract class SchemaWithItemsTest extends TestCase
{
    use SchemaTrait;

    protected static array $migrationsSubfolders = ['items'];

    protected function checkTables(): void
    {
        $this->checkItemsTable();
        $this->checkItemsChildrenTable();
        $this->assertNull($this->getDatabase()->getSchema()->getTableSchema(self::$assignmentsTable));
    }
}
