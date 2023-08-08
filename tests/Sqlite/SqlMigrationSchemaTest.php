<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Sqlite;

class SqlMigrationSchemaTest extends \Yiisoft\Rbac\Db\Tests\Base\SqlMigrationSchemaTest
{
    use DatabaseTrait;
    use SchemaTrait;

    protected static string $driverName = 'sqlite';
}
