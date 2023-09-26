<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\ItemTreeTraversal;

/**
 * A RBAC item tree traversal strategy based on CTE (common table expression) for Oracle.
 *
 * @internal
 */
final class OracleCteItemTreeTraversal extends CteItemTreeTraversal
{
    protected bool $useRecursiveInWith = false;
}
