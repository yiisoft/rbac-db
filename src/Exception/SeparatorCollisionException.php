<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Exception;

use RuntimeException;
use Throwable;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class SeparatorCollisionException extends RuntimeException implements FriendlyExceptionInterface
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Separator collision has been detected.', $code, $previous);
    }

    public function getName(): string
    {
        return 'Separator collision';
    }

    public function getSolution(): ?string
    {
        return <<<SOLUTION
Separator is used to join and split children names during building access tree. It can not be part of item name. Either
customize separator via ItemsStorage::\$namesSeparator or modify existing item names to not contain it.
SOLUTION;
    }
}
