<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/migrations', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // These driver classes are only available when the corresponding optional yiisoft/db-* driver
    // package is installed, which happens on-the-fly in each driver-specific CI workflow
    // (mysql.yml, pgsql.yml, mssql.yml, oracle.yml), not via composer.json.
    ->ignoreUnknownClasses([
        'Yiisoft\Db\Mssql\Connection',
        'Yiisoft\Db\Mssql\Driver',
        'Yiisoft\Db\Mysql\Connection',
        'Yiisoft\Db\Mysql\Driver',
        'Yiisoft\Db\Oracle\Connection',
        'Yiisoft\Db\Oracle\Driver',
        'Yiisoft\Db\Pgsql\Connection',
        'Yiisoft\Db\Pgsql\Driver',
    ]);
