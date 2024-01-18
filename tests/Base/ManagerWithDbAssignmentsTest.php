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

    protected static array $migrationsSubfolders = ['assignments'];

    protected function setUp(): void
    {
        parent::setUp();
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
