<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

abstract class SchemaWithAssignmentsTest extends TestCase
{
    use SchemaTrait;

    protected static array $migrationsSubfolders = ['assignments'];

    protected function checkTables(): void
    {
        $this->checkAssignmentsTable();
        $this->assertNull($this->getDatabase()->getSchema()->getTableSchema(self::$itemsTable));
        $this->assertNull($this->getDatabase()->getSchema()->getTableSchema(self::$itemsChildrenTable));
    }
}
