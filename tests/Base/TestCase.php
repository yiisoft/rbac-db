<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use RuntimeException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\Db\DbSchemaManager;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?ConnectionInterface $database = null;
    private ?Logger $logger = null;

    public function getLogger(): Logger
    {
        if ($this->logger === null) {
            throw new RuntimeException('Logger was not set.');
        }

        return $this->logger;
    }

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

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
        ?string $itemsTable = DbSchemaManager::ITEMS_TABLE,
        ?string $itemsChildrenTable = DbSchemaManager::ITEMS_CHILDREN_TABLE,
        ?string $assignmentsTable = DbSchemaManager::ASSIGNMENTS_TABLE,
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
