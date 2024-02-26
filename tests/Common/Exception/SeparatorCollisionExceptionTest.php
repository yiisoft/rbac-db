<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Common\Exception;

use PHPUnit\Framework\TestCase;
use Yiisoft\Rbac\Db\Exception\SeparatorCollisionException;

final class SeparatorCollisionExceptionTest extends TestCase
{
    public function testGetCode(): void
    {
        $exception = new SeparatorCollisionException();
        $this->assertSame(0, $exception->getCode());
    }

    public function testReturnTypes(): void
    {
        $exception = new SeparatorCollisionException();
        $this->assertIsString($exception->getName());
        $this->assertIsString($exception->getSolution());
    }
}
