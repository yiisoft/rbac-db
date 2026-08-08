<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/migrations', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // ext-pdo is a genuine production dependency (the package stores RBAC data via PDO-based DB
    // connections), it's just not referenced by a PHP symbol in src/ - only in Oracle test setup.
    ->ignoreErrorsOnExtension('ext-pdo', [ErrorType::PROD_DEPENDENCY_ONLY_IN_DEV])
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
