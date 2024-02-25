<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\ItemTreeTraversal;

/**
 * An RBAC item tree traversal strategy based on CTE (common table expression) for Oracle.
 *
 * @internal
 */
final class OracleCteItemTreeTraversal extends CteItemTreeTraversal
{
    protected function getTrimConcatChildrenExpression(): string
    {
        $quoter = $this->database->getQuoter();
        $childrenColumnString = $quoter->quoteColumnName('children');
        $childColumnString = $quoter->quoteTableName('item_child_recursive') . '.' .
            $quoter->quoteColumnName('child');

        return "TRIM ('$this->namesSeparator' FROM $childrenColumnString || '$this->namesSeparator' || " .
            "$childColumnString)";
    }
}
