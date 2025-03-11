<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mssql;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Mssql\Connection;
use Yiisoft\Db\Mssql\Driver;

trait DatabaseTrait
{
    protected function makeDatabase(): ConnectionInterface
    {
        $pdoDriver = new Driver('sqlsrv:Server=127.0.0.1,1433;Database=yiitest;TrustServerCertificate=true', 'SA', 'YourStrong!Passw0rd');
        $pdoDriver->charset('UTF8MB4');

        return new Connection($pdoDriver, new SchemaCache(new ArrayCache()));
    }
}
