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
 * @psalm-import-type AccessTree from ItemTreeTraversalInterface
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
            (SELECT @r := parent FROM $this->childrenTableName WHERE child = child_name LIMIT 1) AS parent
            FROM (SELECT @r := :name) val, $this->childrenTableName
        ) s
        LEFT JOIN $this->tableName AS item ON item.name = s.child_name
        WHERE item.name != :name";

        /** @psalm-var RawItem[] */
        return $this
            ->database
            ->createCommand($sql, [':name' => $name])
            ->queryAll();
    }

    public function getAccessTree(string $name): array
    {
        $sql = "SELECT item.*, access_tree_base.children FROM (
            SELECT child_name, MIN(TRIM(BOTH ',' FROM TRIM(BOTH child_name FROM raw_children))) as children FROM (
                SELECT @r AS child_name, @path := concat(@path, ',', @r) as raw_children,
                (SELECT @r := parent FROM $this->childrenTableName WHERE child = child_name LIMIT 1) AS parent
                FROM (SELECT @r := :name, @path := '') val, $this->childrenTableName
            ) raw_access_tree_base
            GROUP BY child_name
        ) access_tree_base
        LEFT JOIN $this->tableName AS item ON item.name = access_tree_base.child_name";

        /** @psalm-var AccessTree */
        return $this
            ->database
            ->createCommand($sql, [':name' => $name])
            ->queryAll();
    }

    public function getChildrenRows(string|array $names): array
    {
        /** @psalm-var RawItem[] */
        return $this->getChildrenRowsCommand($names, baseOuterQuery: $this->getChildrenBaseOuterQuery())->queryAll();
    }

    public function getChildPermissionRows(string|array $names): array
    {
        $baseOuterQuery = $this->getChildrenBaseOuterQuery()->where(['item.type' => Item::TYPE_PERMISSION]);

        /** @psalm-var RawItem[] */
        return $this->getChildrenRowsCommand($names, baseOuterQuery: $baseOuterQuery)->queryAll();
    }

    public function getChildRoleRows(string|array $names): array
    {
        $baseOuterQuery = $this->getChildrenBaseOuterQuery()->where(['item.type' => Item::TYPE_ROLE]);

        /** @psalm-var RawItem[] */
        return $this->getChildrenRowsCommand($names, baseOuterQuery: $baseOuterQuery)->queryAll();
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

    /**
     * @param string|string[] $names
     */
    private function getChildrenRowsCommand(string|array $names, QueryInterface $baseOuterQuery): CommandInterface
    {
        $names = (array) $names;
        $fromSql = "SELECT DISTINCT child
        FROM (SELECT * FROM $this->childrenTableName ORDER by parent) item_child_sorted,\n";
        $where = '';
        $excludedNamesStr = '';
        $parameters = [];
        $lastNameIndex = array_key_last($names);

        foreach ($names as $index => $name) {
            $fromSql .= "(SELECT @pv$index := :name$index) init$index";
            $excludedNamesStr .= "@pv$index";

            if ($index !== $lastNameIndex) {
                $fromSql .= ',';
                $excludedNamesStr .= ', ';
            }

            $fromSql .= "\n";

            if ($index !== 0) {
                $where .= ' OR ';
            }

            $where .= "(find_in_set(parent, @pv$index) AND length(@pv$index := concat(@pv$index, ',', child)))";

            $parameters[":name$index"] = $name;
        }

        $where = "($where) AND child NOT IN ($excludedNamesStr)";
        $fromSql .= "WHERE $where";
        $outerQuery = $baseOuterQuery
            ->from(new Expression("($fromSql) s"))
            ->leftJoin(['item' => $this->tableName], ['item.name' => new Expression('s.child')]);
        /** @psalm-var non-empty-string $outerQuerySql */
        return $outerQuery->addParams($parameters)->createCommand();
    }

    private function getChildrenBaseOuterQuery(): QueryInterface
    {
        return (new Query($this->database))->select('item.*')->distinct();
    }
}
