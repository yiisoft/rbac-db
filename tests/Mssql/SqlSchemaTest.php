<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mssql;

use Yiisoft\Rbac\Db\Tests\Base\SchemaTrait;

class SqlSchemaTest extends \Yiisoft\Rbac\Db\Tests\Base\SqlSchemaTest
{
    use DatabaseTrait;
    use SchemaTrait;

    protected static string $driverName = 'mssql';
}
