<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Sqlite;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;

trait SqliteTrait
{
    protected function makeDatabase(): ConnectionInterface
    {
        $dbPath = __DIR__ . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'test.db';
        $pdoDriver = new Driver(dsn: "sqlite:$dbPath", username: '', password: '');
        $pdoDriver->charset('UTF8MB4');
        $connection = new Connection($pdoDriver, new SchemaCache(new ArrayCache()));
        $connection->createCommand('PRAGMA foreign_keys = ON;')->execute();

        return $connection;
    }
}
