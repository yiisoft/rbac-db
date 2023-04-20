<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Db\Query\Query;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Tests\Common\ItemsStorageTestTrait;

abstract class ItemsStorageTest extends TestCase
{
    use ItemsStorageTestTrait {
        testClear as protected traitTestClear;
        testRemove as protected traitTestRemove;
        testClearPermissions as protected traitTestClearPermissions;
        testClearRoles as protected traitTestClearRoles;
    }

    public function testClear(): void
    {
        $this->traitTestClear();

        $itemsChildrenExist = (new Query($this->getDatabase()))
            ->from(self::ITEMS_CHILDREN_TABLE)
            ->exists();
        $this->assertSame(false, $itemsChildrenExist);
    }

    public function testRemove(): void
    {
        $storage = $this->getStorage();
        $initialItemChildrenCount = count($storage->getChildren('Parent 2'));

        $this->traitTestRemove();

        $itemsChildren = (new Query($this->getDatabase()))
            ->from(self::ITEMS_CHILDREN_TABLE)
            ->count();
        $this->assertSame($this->initialItemsChildrenCount - $initialItemChildrenCount, $itemsChildren);
    }

    public function testClearPermissions(): void
    {
        $this->traitTestClearPermissions();

        $itemsChildrenCount = (new Query($this->getDatabase()))
            ->from(self::ITEMS_CHILDREN_TABLE)
            ->count();
        $this->assertSame($this->initialBothRolesChildrenCount, $itemsChildrenCount);
    }

    public function testClearRoles(): void
    {
        $this->traitTestClearRoles();

        $itemsChildrenCount = (new Query($this->getDatabase()))
            ->from(self::ITEMS_CHILDREN_TABLE)
            ->count();
        $this->assertSame($this->initialBothPermissionsChildrenCount, $itemsChildrenCount);
    }

    protected function populateDatabase(): void
    {
        $fixtures = $this->getFixtures();

        $this
            ->getDatabase()
            ->createCommand()
            ->batchInsert(self::ITEMS_TABLE, ['name', 'type', 'createdAt', 'updatedAt'], $fixtures['items'])
            ->execute();
        $this
            ->getDatabase()
            ->createCommand()
            ->batchInsert(self::ITEMS_CHILDREN_TABLE, ['parent', 'child'], $fixtures['itemsChildren'])
            ->execute();
    }

    private function getStorage(): ItemsStorageInterface
    {
        return new ItemsStorage(self::ITEMS_TABLE, $this->getDatabase());
    }
}
