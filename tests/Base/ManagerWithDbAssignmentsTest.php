<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Tests\Common\ManagerLogicTestTrait;

abstract class ManagerWithDbAssignmentsTest extends ManagerTest
{
    use ManagerLogicTestTrait {
        setUp as protected traitSetUp;
        tearDown as protected traitTearDown;
    }

    protected function setUp(): void
    {
        $this->createSchemaManager(itemsTable: null, itemsChildrenTable: null)->ensureTables();
        $this->traitSetUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->traitTearDown();
    }

    protected function createAssignmentsStorage(): AssignmentsStorageInterface
    {
        return new AssignmentsStorage($this->getDatabase());
    }
}
