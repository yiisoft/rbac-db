<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\Command\RbacDbInit;
use Yiisoft\Rbac\Db\SchemaManager;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?ConnectionInterface $database = null;

    protected const ITEMS_TABLE = 'auth_item';
    protected const ASSIGNMENTS_TABLE = 'auth_assignment';
    protected const ITEMS_CHILDREN_TABLE = 'auth_item_child';
    private const TABLES_FOR_DROPPING = [self::ITEMS_CHILDREN_TABLE, self::ASSIGNMENTS_TABLE, self::ITEMS_TABLE];

    protected function setUp(): void
    {
        $this->createDatabaseTables();
        $this->populateDatabase();
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES_FOR_DROPPING as $name) {
            $this->getDatabase()->createCommand()->dropTable($name)->execute();
        }
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
        $app = $this->createApplication();
        $app->find('rbac/db/init')->run(new ArrayInput([]), new NullOutput());
    }

    protected function createApplication(string|null $itemsChildrenTable = self::ITEMS_CHILDREN_TABLE): Application
    {
        $app = new Application();
        $schemaManager = new SchemaManager(
            itemsTable: self::ITEMS_TABLE,
            assignmentsTable: self::ASSIGNMENTS_TABLE,
            database: $this->getDatabase(),
            itemsChildrenTable: $itemsChildrenTable,
        );
        $command = new RbacDbInit($schemaManager);
        $app->add($command);

        return $app;
    }
}
