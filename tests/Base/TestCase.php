<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\Db\DbSchemaManager;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const ITEMS_TABLE = 'auth_item';
    protected const ASSIGNMENTS_TABLE = 'auth_assignment';
    protected const ITEMS_CHILDREN_TABLE = 'auth_item_child';

    private ?ConnectionInterface $database = null;

    protected function getDatabase(): ConnectionInterface
    {
        if ($this->database === null) {
            $this->database = $this->makeDatabase();
        }

        return $this->database;
    }

    protected function setUp(): void
    {
        $this->createSchemaManager()->ensureTables();
        $this->populateDatabase();
    }

    protected function tearDown(): void
    {
        $this->createSchemaManager()->ensureNoTables();
        $this->getDatabase()->close();
    }

    protected function createSchemaManager(
        ?string $itemsTable = self::ITEMS_TABLE,
        ?string $itemsChildrenTable = self::ITEMS_CHILDREN_TABLE,
        ?string $assignmentsTable = self::ASSIGNMENTS_TABLE,
    ): DbSchemaManager {
        return new DbSchemaManager(
            database: $this->getDatabase(),
            itemsTable: $itemsTable,
            itemsChildrenTable: $itemsChildrenTable,
            assignmentsTable: $assignmentsTable,
        );
    }

    abstract protected function makeDatabase(): ConnectionInterface;

    abstract protected function populateDatabase(): void;
}
