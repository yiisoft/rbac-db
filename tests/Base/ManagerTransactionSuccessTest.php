<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;

abstract class ManagerTransactionSuccessTest extends ManagerTest
{
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->getDatabase()->setLogger(new NullLogger());
    }

    protected function createItemsStorage(): ItemsStorageInterface
    {
        return new ItemsStorage($this->getDatabase());
    }

    protected function createAssignmentsStorage(): AssignmentsStorageInterface
    {
        return new AssignmentsStorage($this->getDatabase());
    }

    public function testUpdateRoleTransactionSuccess(): void
    {
        $manager = $this->createFilledManager();
        $role = $manager->getRole('reader')->withName('new reader');

        $logger = new Logger();
        $this->getDatabase()->setLogger($logger);

        $manager->updateRole('reader', $role);
        $this->assertTransaction($logger);
    }

    public function testUpdatePermissionTransactionSuccess(): void
    {
        $manager = $this->createFilledManager();
        $permission = $manager->getPermission('updatePost')->withName('newUpdatePost');

        $logger = new Logger();
        $this->getDatabase()->setLogger($logger);

        $manager->updatePermission('updatePost', $permission);
        $this->assertTransaction($logger);
    }

    private function assertTransaction(LoggerInterface $logger): void
    {
        $result = false;

        foreach ($logger->getMessages() as $message) {
            if (str_starts_with($message, 'Commit transaction')) {
                $result = true;

                break;
            }
        }

        $this->assertTrue($result);
    }
}
