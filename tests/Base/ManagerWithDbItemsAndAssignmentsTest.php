<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Tests\Common\ManagerLogicTestTrait;

abstract class ManagerWithDbItemsAndAssignmentsTest extends ManagerTest
{
    use ManagerLogicTestTrait;

    protected function createItemsStorage(): ItemsStorageInterface
    {
        return new ItemsStorage($this->getDatabase());
    }

    protected function createAssignmentsStorage(): AssignmentsStorageInterface
    {
        return new AssignmentsStorage($this->getDatabase());
    }
}
