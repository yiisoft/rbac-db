<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mysql;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Db\Mysql\Driver;
use Yiisoft\Rbac\Db\Tests\Base\Logger;

trait DatabaseTrait
{
    protected function makeDatabase(): ConnectionInterface
    {
        $pdoDriver = new Driver('mysql:host=127.0.0.1;dbname=yiitest;port=3306', 'root');
        $pdoDriver->charset('UTF8MB4');

        $connection = Connection($pdoDriver, new SchemaCache(new ArrayCache()));

        $logger = new Logger();
        $connection->setLogger($logger);
        $this->setLogger($logger);

        return $connection;
    }
}
