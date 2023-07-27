<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\ItemTreeTraversal;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\Db\ItemsStorage;

/**
 * A RBAC item tree traversal strategy based on CTE (common table expression). Uses `WITH` expression to form a
 * recursive query. The base queries are unified as much possible to work for all RDBMS supported by Yii Database with
 * minimal differences.
 *
 * @internal
 *
 * @psalm-import-type RawItem from ItemsStorage
 */
abstract class CteItemTreeTraversal implements ItemTreeTraversalInterface
{
    /**
     * @param ConnectionInterface $database Yii Database connection instance.
     *
     * @param string $tableName A name of the table for storing RBAC items.
     * @psalm-param non-empty-string $tableName
     *
     * @param string $childrenTableName A name of the table for storing relations between RBAC items.
     * @psalm-param non-empty-string $childrenTableName
     */
    public function __construct(
        protected ConnectionInterface $database,
        protected string $tableName,
        protected string $childrenTableName,
    ) {
    }

    public function getParentRows(string $name): array
    {
        $sql = "{$this->getWithExpression()} parent_of(child_name) AS (
            SELECT [[name]] FROM {{%$this->tableName}} WHERE [[name]] = :name_for_recursion
            UNION ALL
            SELECT [[parent]] FROM {{%$this->childrenTableName}} item_child_recursive, parent_of
            WHERE item_child_recursive.[[child]] = parent_of.child_name
        )
        SELECT {{%item}}.* FROM parent_of
        LEFT JOIN {{%$this->tableName}} {{%item}} ON {{%item}}.[[name]] = parent_of.child_name
        WHERE {{%item}}.[[name]] != :excluded_name";

        /** @psalm-var RawItem[] */
        return $this
            ->database
            ->createCommand($sql, [':name_for_recursion' => $name, ':excluded_name' => $name])
            ->queryAll();
    }

    public function getChildrenRows(string $name): array
    {
        $sql = "{$this->getWithExpression()} child_of(parent_name) AS (
            SELECT [[name]] FROM {{%$this->tableName}} WHERE [[name]] = :name_for_recursion
            UNION ALL
            SELECT [[child]] FROM {{%$this->childrenTableName}} item_child_recursive, child_of
            WHERE item_child_recursive.[[parent]] = child_of.parent_name
        )
        SELECT {{%item}}.* FROM child_of
        LEFT JOIN {{%$this->tableName}} {{%item}} ON {{%item}}.[[name]] = child_of.parent_name
        WHERE {{%item}}.[[name]] != :excluded_name";

        /** @psalm-var RawItem[] */
        return $this
            ->database
            ->createCommand($sql, [':name_for_recursion' => $name, ':excluded_name' => $name])
            ->queryAll();
    }

    /**
     * Gets `WITH` expression used in DB query.
     *
     * @infection-ignore-all
     * - ProtectedVisibility.
     *
     * @return string `WITH` expression.
     */
    protected function getWithExpression(): string
    {
        return 'WITH RECURSIVE';
    }
}
