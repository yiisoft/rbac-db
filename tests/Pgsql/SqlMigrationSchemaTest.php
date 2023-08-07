<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Pgsql;

use Yiisoft\Rbac\Db\Tests\Base\SchemaTrait;

class SqlMigrationSchemaTest extends \Yiisoft\Rbac\Db\Tests\Base\SqlMigrationSchemaTest
{
    use DatabaseTrait;
    use SchemaTrait;

    protected static string $driverName = 'pgsql';
}
