<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mysql;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;

trait MysqlTrait
{
    protected function makeDatabase(): ConnectionInterface
    {
        $pdoDriver = new Driver('mysql:host=127.0.0.1;dbname=yiitest;port=3306', 'root');
        $pdoDriver->charset('UTF8MB4');

        return new Connection($pdoDriver, new SchemaCache(new ArrayCache()));
    }
}
