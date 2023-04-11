<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\ItemTreeTraversal;

use Yiisoft\Db\Schema\ColumnSchemaInterface;

/**
 * A RBAC item tree traversal strategy based on CTE (common table expression) for MySQL 8 and above (lower versions
 * don't support this functionality).
 *
 * @internal
 */
final class MysqlCteItemTreeTraversal extends CteItemTreeTraversal
{
    protected function getCastedNameType(ColumnSchemaInterface $column): string
    {
        return "char({$column->getSize()})";
    }
}
