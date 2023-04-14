<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Oracle;

use PDO;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Oracle\Connection;
use Yiisoft\Db\Oracle\Driver;

trait OracleTrait
{
    protected function makeDatabase(): ConnectionInterface
    {
        $pdoDriver = new Driver('oci:dbname=localhost/XE;', 'system', 'oracle');
        $pdoDriver->charset('AL32UTF8');
        $pdoDriver->attributes([PDO::ATTR_STRINGIFY_FETCHES => true]);

        return new Connection($pdoDriver, new SchemaCache(new ArrayCache()));
    }
}
