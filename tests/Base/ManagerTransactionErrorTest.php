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
        return new ItemsStorage(self::ITEMS_TABLE, $this->getDatabase(), self::ITEMS_CHILDREN_TABLE);
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
        $role = $this->itemsStorage->getRole('reader')->withName('new reader');

        try {
            $manager->updateRole('reader', $role);
        } catch (RuntimeException) {
            $this->assertNotNull($this->itemsStorage->getRole('reader'));
            $this->assertNull($this->itemsStorage->getRole('new reader'));
        }
    }

    public function testUpdatePermissionTransactionError(): void
    {
        $manager = $this->createFilledManager();
        $permission = $this->itemsStorage->getPermission('updatePost')->withName('newUpdatePost');

        try {
            $manager->updatePermission('updatePost', $permission);
        } catch (RuntimeException) {
            $this->assertNotNull($this->itemsStorage->getPermission('updatePost'));
            $this->assertNull($this->itemsStorage->getPermission('newUpdatePost'));
        }
    }
}
