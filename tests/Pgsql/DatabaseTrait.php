<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Pgsql;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Pgsql\Connection;
use Yiisoft\Db\Pgsql\Driver;
use Yiisoft\Rbac\Db\Tests\Base\Logger;

trait DatabaseTrait
{
    protected function makeDatabase(): ConnectionInterface
    {
        $pdoDriver = new Driver('pgsql:host=127.0.0.1;dbname=yiitest;port=5432', 'root', 'root');
        $pdoDriver->charset('UTF8');

        $connection = Connection($pdoDriver, new SchemaCache(new ArrayCache()));

        $logger = new Logger();
        $connection->setLogger($logger);
        $this->setLogger($logger);

        return $connection;
    }
}
