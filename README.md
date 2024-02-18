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
[![codecov](https://codecov.io/gh/yiisoft/rbac-db/graph/badge.svg?token=YU8LVBNCQ8)](https://codecov.io/gh/yiisoft/rbac-db)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Frbac-db%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/rbac-db/master)
[![static analysis](https://github.com/yiisoft/rbac-db/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/rbac-db/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/rbac-db/coverage.svg)](https://shepherd.dev/github/yiisoft/rbac-db)

Detailed build statuses:

| RDBMS                | Status                                                                                                                                                |
|----------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------|
| SQLite               | [![SQLite status](https://github.com/yiisoft/rbac-db/workflows/sqlite/badge.svg)](https://github.com/yiisoft/rbac-db/actions?query=workflow%3Asqlite) |
| MySQL                | [![MYSQL status](https://github.com/yiisoft/rbac-db/workflows/mysql/badge.svg)](https://github.com/yiisoft/rbac-db/actions?query=workflow%3Amysql)    |
| PostgreSQL           | [![MYSQL status](https://github.com/yiisoft/rbac-db/workflows/pgsql/badge.svg)](https://github.com/yiisoft/rbac-db/actions?query=workflow%3Apgsql)    |
| Microsoft SQL Server | [![MYSQL status](https://github.com/yiisoft/rbac-db/workflows/mssql/badge.svg)](https://github.com/yiisoft/rbac-db/actions?query=workflow%3Amssql)    |
| Oracle               | [![MYSQL status](https://github.com/yiisoft/rbac-db/workflows/oracle/badge.svg)](https://github.com/yiisoft/rbac-db/actions?query=workflow%3Aoracle)  |

## Requirements

- PHP 8.1 or higher.
- `PDO` PHP extension.
- One of the following drivers:
  - [SQLite](https://github.com/yiisoft/db-sqlite) (minimal required version is 3.8.3)
  - [MySQL](https://github.com/yiisoft/db-mysql)
  - [PostgreSQL](https://github.com/yiisoft/db-pgsql)
  - [Microsoft SQL Server](https://github.com/yiisoft/db-mssql)
  - [Oracle](https://github.com/yiisoft/db-oracle)
- `PDO` PHP extension for the selected driver.
- In case of using with SQL Server, minimal required version of PDO is 5.11.1.

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

### Working with migrations

This package uses [Yii DB Migration](https://github.com/yiisoft/db-migration) for managing database tables required for
storages. There are 3 tables in total (`yii_rbac_` prefix is used).

Items storage:

- `yii_rbac_item`.
- `yii_rbac_item_child`.

Assignments storage:

- `yii_rbac_assignment`.

#### Configuring migrations

Make sure to include these directories as source paths:

- [migrations/items](./migrations/items);
- [migrations/assignments](./migrations/assignments).

When using [Yii Console](https://github.com/yiisoft/yii-console), add this to `config/params.php`:

```php
'yiisoft/db-migration' => [
    // ...
    'sourcePaths' => [
        dirname(__DIR__) . '/vendor/yiisoft/rbac-db/migrations/items']),
        dirname(__DIR__) . '/vendor/yiisoft/rbac-db/migrations/assignments']),
    ],
],
```

and database connection configuration from [previous section](#configuring-database-connection) to DI container 
(`config/common/db.php)`:

```php
<?php

declare(strict_types=1);

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Pgsql\Connection as PgsqlConnection;

return [
    ConnectionInterface::class => [
        'class' => PgsqlConnection::class,
        '__construct()' => [
            // ...
        ],
    ]
];
```

Because item and assignment storages are completely indepedent, migrations are separated as well in order to prevent
creation of unused tables. So, for example, if you only want to use assignment storage, add only 
[migrations/assignments](./migrations/assignments) to source paths.

Other ways of using migrations are covered [here](https://github.com/yiisoft/db-migration#usage).

#### Applying migrations

Using with [Yii Console](https://github.com/yiisoft/yii-console):

```shell
./yii migrate:up
```

Other ways of using migrations are covered [here](https://github.com/yiisoft/db-migration#usage).

#### Reverting migrations

Using with [Yii Console](https://github.com/yiisoft/yii-console):

```shell
./yii migrate:down --limit=2
```

Other ways of using migrations are covered [here](https://github.com/yiisoft/db-migration#usage).

### Using storages

The storages are not intended to be used directly. Instead, use them with `Manager` from
[Yii RBAC](https://github.com/yiisoft/rbac) package:

```php
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\Db\TransactionalManagerDecorator;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\RuleFactoryInterface;

/** @var ConnectionInterface $database */
$itemsStorage = new ItemsStorage($database);
$assignmentsStorage = new AssignmentsStorage($database);
/** @var RuleFactoryInterface $rulesContainer */
$manager = new TransactionalManagerDecorator(
    new Manager(
        itemsStorage: $itemsStorage, 
        assignmentsStorage: $assignmentsStorage,
        // Requires https://github.com/yiisoft/rbac-rules-container or other compatible factory.
        ruleFactory: $rulesContainer,
    ),
);
$manager->addPermission(new Permission('posts.create'));
```

> Note wrapping manager with decorator - it additionally provides database transactions to guarantee data integrity.

> Note that it's not necessary to use both DB storages. Combining different implementations is possible. A quite popular 
> case is to manage items via [PHP files](https://github.com/yiisoft/rbac-php) while store assignments in database.

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
