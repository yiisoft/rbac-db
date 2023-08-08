<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Oracle;

use PDO;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Oracle\Driver;
use Yiisoft\Rbac\Db\Tests\Base\Logger;

trait DatabaseTrait
{
    protected function makeDatabase(): ConnectionInterface
    {
        $pdoDriver = new Driver('oci:dbname=localhost/XE;', 'system', 'root');
        $pdoDriver->charset('AL32UTF8');
        $pdoDriver->attributes([PDO::ATTR_STRINGIFY_FETCHES => true]);

        $connection = Connection($pdoDriver, new SchemaCache(new ArrayCache()));

        $logger = new Logger();
        $connection->setLogger($logger);
        $this->setLogger($logger);

        return $connection;
    }
}
