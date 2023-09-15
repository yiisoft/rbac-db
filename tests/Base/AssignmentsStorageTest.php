<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\DbSchemaManager;
use Yiisoft\Rbac\Tests\Common\AssignmentsStorageTestTrait;

abstract class AssignmentsStorageTest extends TestCase
{
    use AssignmentsStorageTestTrait;

    protected function populateDatabase(): void
    {
        $fixtures = $this->getFixtures();

        $this->getDatabase()
            ->createCommand()
            ->batchInsert(
                DbSchemaManager::ITEMS_TABLE,
                ['name', 'type', 'createdAt', 'updatedAt'],
                $fixtures['items'],
            )
            ->execute();
        $this->getDatabase()
            ->createCommand()
            ->batchInsert(
                DbSchemaManager::ASSIGNMENTS_TABLE,
                ['itemName', 'userId', 'createdAt'],
                $fixtures['assignments'],
            )
            ->execute();
    }

    private function getStorage(): AssignmentsStorageInterface
    {
        return new AssignmentsStorage($this->getDatabase());
    }
}
