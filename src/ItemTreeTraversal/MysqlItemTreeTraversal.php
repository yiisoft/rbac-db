<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\ItemTreeTraversal;

use Yiisoft\Db\Command\CommandInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Query\Query;
use Yiisoft\Db\Query\QueryInterface;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\Item;

/**
 * A RBAC item tree traversal strategy based on specific functionality for MySQL 5, without support for CTE (Common
 * Table Expressions).
 *
 * @internal
 *
 * @psalm-import-type RawItem from ItemsStorage
 */
final class MysqlItemTreeTraversal implements ItemTreeTraversalInterface
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
        $sql = "SELECT DISTINCT item.* FROM (
            SELECT @r AS child_name,
            (SELECT @r := parent FROM $this->childrenTableName WHERE child = child_name LIMIT 1) AS parent,
            @l := @l + 1 AS level
            FROM (SELECT @r := :name, @l := 0) val, $this->childrenTableName
        ) s
        LEFT JOIN $this->tableName AS item ON item.name = s.child_name
        WHERE item.name != :name";

        /** @psalm-var RawItem[] */
        return $this
            ->database
            ->createCommand($sql, [':name' => $name])
            ->queryAll();
    }

    public function getChildrenRows(string $name): array
    {
        $baseOuterQuery = (new Query($this->database))->select([new Expression('item.*')])->distinct();

        /** @psalm-var RawItem[] */
        return $this->getChildrenRowsCommand($name, baseOuterQuery: $baseOuterQuery)->queryAll();
    }

    public function getChildPermissionRows(string $name): array
    {
        $baseOuterQuery = (new Query($this->database))
            ->select([new Expression('item.*')])
            ->distinct()
            ->where(['item.type' => Item::TYPE_PERMISSION]);

        /** @psalm-var RawItem[] */
        return $this->getChildrenRowsCommand($name, baseOuterQuery: $baseOuterQuery)->queryAll();
    }

    public function getChildRoleRows(string $name): array
    {
        $baseOuterQuery = (new Query($this->database))
            ->select([new Expression('item.*')])
            ->distinct()
            ->where(['item.type' => Item::TYPE_ROLE]);

        /** @psalm-var RawItem[] */
        return $this->getChildrenRowsCommand($name, baseOuterQuery: $baseOuterQuery)->queryAll();
    }

    public function hasChild(string $parentName, string $childName): bool
    {
        $baseOuterQuery = (new Query($this->database))
            ->select([new Expression('1 AS item_child_exists')])
            ->andWhere(['item.name' => $childName]);
        /** @psalm-var array<0, 1>|false $result */
        $result = $this->getChildrenRowsCommand($parentName, baseOuterQuery: $baseOuterQuery)->queryScalar();

        return $result !== false;
    }

    private function getChildrenRowsCommand(string $name, QueryInterface $baseOuterQuery): CommandInterface
    {
        $fromSql = "SELECT DISTINCT child
        FROM (SELECT * FROM $this->childrenTableName ORDER by parent) item_child_sorted,
        (SELECT @pv := :name) init
        WHERE find_in_set(parent, @pv) AND length(@pv := concat(@pv, ',', child))";
        $outerQuery = $baseOuterQuery
            ->from(['s' => "($fromSql)"])
            ->leftJoin($this->tableName . ' AS item', ['item.name' => new Expression('s.child')])
            ->addParams([':name' => $name]);

        return $outerQuery->createCommand();
    }
}
