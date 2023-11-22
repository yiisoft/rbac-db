<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\DbSchemaManager;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Tests\Common\AssignmentsStorageTestTrait;

abstract class AssignmentsStorageTest extends TestCase
{
    use AssignmentsStorageTestTrait {
        setUp as protected traitSetUp;
        tearDown as protected traitTearDown;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->traitSetUp();
    }

    protected function tearDown(): void
    {
        $this->traitTearDown();
        parent::tearDown();
    }

    protected function populateItemsStorage(): void
    {
        $this->getDatabase()
            ->createCommand()
            ->batchInsert(
                DbSchemaManager::ITEMS_TABLE,
                ['name', 'type', 'createdAt', 'updatedAt'],
                $this->getFixtures()['items'],
            )
            ->execute();
    }

    protected function populateAssignmentsStorage(): void
    {
        $this->getDatabase()
            ->createCommand()
            ->batchInsert(
                DbSchemaManager::ASSIGNMENTS_TABLE,
                ['itemName', 'userId', 'createdAt'],
                $this->getFixtures()['assignments'],
            )
            ->execute();
    }

    protected function populateDatabase(): void
    {
        // Skip
    }

    protected function createItemsStorage(): ItemsStorageInterface
    {
        return new ItemsStorage($this->getDatabase());
    }

    protected function createAssignmentsStorage(): AssignmentsStorageInterface
    {
        return new AssignmentsStorage($this->getDatabase());
    }
}
