<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Command;

/**
 * Command for creating RBAC related database tables using Yii Database.
 */
final class RbacDbInit extends \Yiisoft\Rbac\Command\RbacDbInit
{
    protected static $defaultName = 'rbac/db/init';

    protected function configure(): void
    {
        parent::configure();

        $this->setHelp('This command creates schemas for RBAC using Yii Database');
    }
}
