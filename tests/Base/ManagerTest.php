<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\TransactionalManagerDecorator;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Tests\Common\ManagerConfigurationTestTrait;

abstract class ManagerTest extends TestCase
{
    use ManagerConfigurationTestTrait {
        createManager as protected traitCreateManager;
    }

    protected function tearDown(): void
    {
        $this->createItemsStorage()->clear();
        $this->createAssignmentsStorage()->clear();

        parent::tearDown();
    }

    protected function populateDatabase(): void
    {
        // Skip
    }

    protected function createManager(
        ?ItemsStorageInterface $itemsStorage = null,
        ?AssignmentsStorageInterface $assignmentsStorage = null,
        ?bool $enableDirectPermissions = false
    ): ManagerInterface {
        return new TransactionalManagerDecorator(
            $this->traitCreateManager($itemsStorage, $assignmentsStorage, $enableDirectPermissions),
            $this->getDatabase(),
        );
    }
}
