<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\TransactionalManagerDecorator;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\RuleFactoryInterface;
use Yiisoft\Rbac\Tests\Common\ManagerConfigurationTestTrait;
use Yiisoft\Rbac\Tests\Support\SimpleRuleFactory;

abstract class ManagerTest extends TestCase
{
    use ManagerConfigurationTestTrait;

    protected function populateDatabase(): void
    {
        // Skip
    }

    protected function createManager(
        ?ItemsStorageInterface $itemsStorage = null,
        ?AssignmentsStorageInterface $assignmentsStorage = null,
        ?RuleFactoryInterface $ruleFactory = null,
        ?bool $enableDirectPermissions = false
    ): ManagerInterface {
        $arguments = [
            $itemsStorage ?? $this->createItemsStorage(),
            $assignmentsStorage ?? $this->createAssignmentsStorage(),
            $ruleFactory ?? new SimpleRuleFactory(),
        ];
        if ($enableDirectPermissions !== null) {
            $arguments[] = $enableDirectPermissions;
        }

        return new TransactionalManagerDecorator(new Manager(...$arguments), $this->getDatabase());
    }
}
