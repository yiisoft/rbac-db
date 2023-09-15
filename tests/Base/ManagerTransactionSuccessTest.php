<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;

abstract class ManagerTransactionSuccessTest extends ManagerTest
{
    protected function setUp(): void
    {
        $this->createSchemaManager()->ensureTables();
    }

    protected function createItemsStorage(): ItemsStorageInterface
    {
        return new ItemsStorage($this->getDatabase());
    }

    protected function createAssignmentsStorage(): AssignmentsStorageInterface
    {
        return new AssignmentsStorage($this->getDatabase());
    }

    public function testUpdateRoleTransactionError(): void
    {
        $manager = $this->createFilledManager();
        $role = $this->itemsStorage->getRole('reader')->withName('new reader');
        $manager->updateRole('reader', $role);

        $this->assertTransaction();
    }

    public function testUpdatePermissionTransactionError(): void
    {
        $manager = $this->createFilledManager();
        $permission = $this->itemsStorage->getPermission('updatePost')->withName('newUpdatePost');
        $manager->updatePermission('updatePost', $permission);

        $this->assertTransaction();
    }

    private function assertTransaction(): void
    {
        $result = false;

        foreach ($this->getLogger()->getMessages() as $message) {
            if (str_starts_with($message, 'Commit transaction')) {
                $result = true;

                break;
            }
        }

        $this->assertTrue($result);
    }
}
