<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;

abstract class ManagerWithDbItemsTest extends ManagerTest
{
    protected static array $migrationsSubfolders = ['items'];

    protected function createItemsStorage(): ItemsStorageInterface
    {
        return new ItemsStorage($this->getDatabase());
    }
}
