<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Mssql;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Mssql\Connection;
use Yiisoft\Db\Mssql\Driver;
use Yiisoft\Rbac\Db\Tests\Base\Logger;

trait DatabaseTrait
{
    protected function makeDatabase(): ConnectionInterface
    {
        $pdoDriver = new Driver('sqlsrv:Server=127.0.0.1,1433;Database=yiitest', 'SA', 'YourStrong!Passw0rd');
        $pdoDriver->charset('UTF8MB4');

        $connection = new Connection($pdoDriver, new SchemaCache(new ArrayCache()));

        $logger = new Logger();
        $connection->setLogger($logger);
        $this->setLogger($logger);

        return $connection;
    }
}
