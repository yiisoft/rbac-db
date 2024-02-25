<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\ItemTreeTraversal;

/**
 * An RBAC item tree traversal strategy based on CTE (common table expression) for SQL Server.
 *
 * @internal
 */
final class MssqlCteItemTreeTraversal extends CteItemTreeTraversal
{
    protected function getEmptyChildrenExpression(): string
    {
        return "CAST('' AS NVARCHAR(MAX))";
    }
}
