<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://github.com/yiisoft.png" height="100px">
    </a>
    <h1 align="center">Yii RBAC Database</h1>
    <br>
</p>

The package provides [Yii Database](https://github.com/yiisoft/db) storage for 
[Yii RBAC](https://github.com/yiisoft/rbac).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/rbac-db/v/stable.png)](https://packagist.org/packages/yiisoft/rbac-db)
[![Total Downloads](https://poser.pugx.org/yiisoft/rbac-db/downloads.png)](https://packagist.org/packages/yiisoft/rbac-db)
[![Build status](https://github.com/yiisoft/rbac-db/workflows/build/badge.svg)](https://github.com/yiisoft/rbac-db/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/rbac-db/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/rbac-db/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/rbac-db/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/rbac-db/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Frbac-db%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/rbac-db/master)
[![static analysis](https://github.com/yiisoft/rbac-db/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/rbac-db/actions?query=workflow%3A%22static+analysis%22)

## Requirements

- PHP 8.0 or higher.
- `PDO` PHP extension.
- One of the following drivers:
  - [SQLite](https://github.com/yiisoft/db-sqlite) (minimal required version is 3.8.3)
  - [MySQL](https://github.com/yiisoft/db-mysql)
  - [PostgreSQL](https://github.com/yiisoft/db-pgsql)
  - [Microsoft SQL Server](https://github.com/yiisoft/db-mssql)
  - [Oracle](https://github.com/yiisoft/db-oracle)
- `PDO` PHP extension for the selected driver.

## Installation

The package could be installed with composer:

```shell
composer require yiisoft/rbac-db
```

## General usage

### Configuring database connection

Configuration depends on a selected driver. Here is an example for PostgreSQL:

```php
use Yiisoft\Cache\ArrayCache; // Requires https://github.com/yiisoft/cache
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Pgsql\Connection;
use Yiisoft\Db\Pgsql\Driver;

$pdoDriver = new Driver('pgsql:host=127.0.0.1;dbname=yiitest;port=5432', 'user', 'password');
$pdoDriver->charset('UTF8');
$connection = Connection(
    $pdoDriver, 
    new SchemaCache(
        new ArrayCache(), // Any other PSR-16 compatible cache can be used.
    )
);
```

More comprehensive examples can be found at 
[Yii Database docs](https://github.com/yiisoft/db/blob/master/docs/en/README.md#prerequisites).

### Working with schema

In order to keep less dependencies, this package doesn't provide any CLI for working with schema. There are multiple 
options to choose from:

- Use migration tool like [Yii DB Migration](https://github.com/yiisoft/yii-db-migration). Migrations are dumped as 
plain SQL in `sql/migrations` folder. In case of updating `rbac-db` package, you only need to run migrate command.
- Without migrations, `SchemaManager` class can be used. An example of CLI command containing it can be found 
[here](examples/Command/RbacDbInit.php). In case of updating `rbac-db` package, you need to find out and apply the
changes manually.

`SchemaManager` can also be useful if you need to customize table names:

```php
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\Db\SchemaManager;

/** @var ConnectionInterface $database */
$schemaManager = new SchemaManager(
    itemsTable: 'custom_items',
    assignmentsTable: 'custom_assignments',
    database: $database,
    itemsChildrenTable: 'custom_items_children',
);
$schemaManager->createAll();
$schemaManager->dropAll(); // Note: All existing data will be erased.
```

### Using storages

The storages are not intended to be used directly. Instead, use them with `Manager` from
[Yii RBAC](https://github.com/yiisoft/rbac) package:

```php
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\Manager;use Yiisoft\Rbac\Permission;use Yiisoft\Rbac\RuleFactoryInterface;

/** @var ConnectionInterface $database */
$itemsStorage = new ItemsStorage(
    tableName: 'auth_item',
    database: $database,
    childrenTableName: 'auth_item_child', // Optional, will be generated automatically when empty. 
);
$assignmentsStorage = new AssignmentsStorage(
    tableName: 'auth_assignment',
    database: $database,
);
/** @var RuleFactoryInterface $rulesContainer */
$manager = new Manager(
    itemsStorage: $itemsStorage, 
    assignmentsStorage: $assignmentsStorage,
    ruleFactory: $rulesContainer, // Requires https://github.com/yiisoft/rbac-rules-container
);
$manager->addPermission(new Permission('posts.create'));
```

More examples can be found in [Yii RBAC](https://github.com/yiisoft/rbac) documentation.

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```php
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```php
./vendor/bin/infection
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev). To run static analysis:

```php
./vendor/bin/psalm
```
