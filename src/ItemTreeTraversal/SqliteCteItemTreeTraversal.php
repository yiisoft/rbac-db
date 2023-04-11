<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\ItemTreeTraversal;

/**
 * A RBAC item tree traversal strategy based on CTE (common table expression) for SQLite. Should be used only with
 * versions >= 3.8.3 (lower versions don't support this functionality).
 *
 * @internal
 */
final class SqliteCteItemTreeTraversal extends CteItemTreeTraversal
{
}
