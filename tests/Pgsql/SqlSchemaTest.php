<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Pgsql;

class SqlSchemaTest extends \Yiisoft\Rbac\Db\Tests\Base\SqlSchemaTest
{
    use DatabaseTrait;
    use SchemaTrait;

    protected static string $driverName = 'pgsql';
}
