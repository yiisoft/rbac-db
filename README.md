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
  - [SQLite](https://github.com/yiisoft/sqlite) (minimal required version is 3.8.3)
  - [MySQL](https://github.com/yiisoft/mysql)
  - [PostgreSQL](https://github.com/yiisoft/pgsql)
  - [Microsoft SQL Server](https://github.com/yiisoft/mssql)
  - [Oracle](https://github.com/yiisoft/oracle)
- `PDO` PHP extension for the selected driver.

## Installation

The package could be installed with composer:

```shell
composer require yiisoft/rbac-db
```

## General usage

```shell
rbac/db/init
```

By default, when called repeatedly, the creation of tables will be skipped if they are already exist. The `--force` flag
can be added to force dropping of existing tables and recreate them:

```shell
rbac/db/init --force
```

> Note: All existing data will be erased.

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
