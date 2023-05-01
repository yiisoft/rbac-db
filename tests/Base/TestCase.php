<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\Db\SchemaManager;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?ConnectionInterface $database = null;

    protected const ITEMS_TABLE = 'auth_item';
    protected const ASSIGNMENTS_TABLE = 'auth_assignment';
    protected const ITEMS_CHILDREN_TABLE = 'auth_item_child';

    protected function setUp(): void
    {
        $this->createDatabaseTables();
        $this->populateDatabase();
    }

    protected function tearDown(): void
    {
        $this->createSchemaManager()->dropAll();
    }

    abstract protected function makeDatabase(): ConnectionInterface;

    abstract protected function populateDatabase(): void;

    protected function getDatabase(): ConnectionInterface
    {
        if ($this->database === null) {
            $this->database = $this->makeDatabase();
        }

        return $this->database;
    }

    protected function createDatabaseTables(): void
    {
        $this->createSchemaManager()->createAll();
    }

    protected function createSchemaManager(string|null $itemsChildrenTable = self::ITEMS_CHILDREN_TABLE): SchemaManager
    {
        return new SchemaManager(
            itemsTable: self::ITEMS_TABLE,
            assignmentsTable: self::ASSIGNMENTS_TABLE,
            database: $this->getDatabase(),
            itemsChildrenTable: $itemsChildrenTable,
        );
    }
}
