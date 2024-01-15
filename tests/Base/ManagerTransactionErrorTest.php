<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use RuntimeException;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Tests\Support\FakeAssignmentsStorage;

abstract class ManagerTransactionErrorTest extends ManagerTest
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
        return new class () extends FakeAssignmentsStorage {
            public function renameItem(string $oldName, string $newName): void
            {
                throw new RuntimeException('Failed to rename item.');
            }
        };
    }

    public function testUpdateRoleTransactionError(): void
    {
        $manager = $this->createFilledManager();
        $role = $manager->getRole('reader')->withName('new reader');

        try {
            $manager->updateRole('reader', $role);
        } catch (RuntimeException) {
            $this->assertNotNull($manager->getRole('reader'));
            $this->assertNull($manager->getRole('new reader'));
        }
    }

    public function testUpdatePermissionTransactionError(): void
    {
        $manager = $this->createFilledManager();
        $permission = $manager->getPermission('updatePost')->withName('newUpdatePost');

        try {
            $manager->updatePermission('updatePost', $permission);
        } catch (RuntimeException) {
            $this->assertNotNull($manager->getPermission('updatePost'));
            $this->assertNull($manager->getPermission('newUpdatePost'));
        }
    }
}
