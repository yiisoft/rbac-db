<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mssql;

class SqlSchemaTest extends \Yiisoft\Rbac\Db\Tests\Base\SqlSchemaTest
{
    use DatabaseTrait;

    protected static string $driverName = 'mssql';
}
