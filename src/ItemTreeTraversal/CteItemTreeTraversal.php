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
 * A RBAC item tree traversal strategy based on CTE (common table expression). Uses `WITH` expression to form a
 * recursive query. The base queries are unified as much possible to work for all RDBMS supported by Yii Database with
 * minimal differences.
 *
 * @internal
 *
 * @psalm-import-type RawItem from ItemsStorage
 * @psalm-import-type AccessTree from ItemTreeTraversalInterface
 */
abstract class CteItemTreeTraversal implements ItemTreeTraversalInterface
{
    protected bool $useRecursiveInWith = true;

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
        $baseOuterQuery = (new Query($this->database))->select('item.*')->where(['!=', 'item.name', $name]);

        /** @psalm-var RawItem[] */
        return $this->getRowsCommand($name, baseOuterQuery: $baseOuterQuery)->queryAll();
    }

    public function getAccessTree(string $name): array
    {
        $baseOuterQuery = (new Query($this->database))->select(['item.*', 'parent_of.children']);
        $cteSelectRelationQuery = (new Query($this->database))
            ->select(['parent', new Expression($this->getTrimConcatChildrenExpression())])
            ->from("$this->childrenTableName AS item_child_recursive")
            ->innerJoin('parent_of', [
                "item_child_recursive.child" => new Expression('{{parent_of}}.[[child_name]]'),
            ]);
        $cteSelectItemQuery = (new Query($this->database))
            ->select(['name', new Expression($this->getEmptyChildrenExpression())])
            ->from($this->tableName)
            ->where(['name' => $name])
            ->union($cteSelectRelationQuery, all: true);
        $quoter = $this->database->getQuoter();
        $outerQuery = $baseOuterQuery
            ->withQuery(
                $cteSelectItemQuery,
                $quoter->quoteTableName('parent_of') . '(' . $quoter->quoteColumnName('child_name') . ',' .
                $quoter->quoteColumnName('children') . ')',
                recursive: $this->useRecursiveInWith,
            )
            ->from('parent_of')
            ->leftJoin(
                ['item' => $this->tableName],
                ['item.name' => new Expression('{{parent_of}}.[[child_name]]')],
            );

        /** @psalm-var AccessTree */
        return $outerQuery->createCommand()->queryAll();
    }

    public function getChildrenRows(string|array $names): array
    {
        $baseOuterQuery = $this->getChildrenBaseOuterQuery($names);

        /** @psalm-var RawItem[] */
        return $this->getRowsCommand($names, baseOuterQuery: $baseOuterQuery, areParents: false)->queryAll();
    }

    public function getChildPermissionRows(string|array $names): array
    {
        $baseOuterQuery = $this->getChildrenBaseOuterQuery($names)->andWhere(['item.type' => Item::TYPE_PERMISSION]);

        /** @psalm-var RawItem[] */
        return $this->getRowsCommand($names, baseOuterQuery: $baseOuterQuery, areParents: false)->queryAll();
    }

    public function getChildRoleRows(string|array $names): array
    {
        $baseOuterQuery = $this->getChildrenBaseOuterQuery($names)->andWhere(['item.type' => Item::TYPE_ROLE]);

        /** @psalm-var RawItem[] */
        return $this->getRowsCommand($names, baseOuterQuery: $baseOuterQuery, areParents: false)->queryAll();
    }

    public function hasChild(string $parentName, string $childName): bool
    {
        /**
         * @infection-ignore-all
         * - ArrayItemRemoval, select.
         */
        $baseOuterQuery = (new Query($this->database))
            ->select([new Expression('1 AS item_child_exists')])
            ->andWhere(['item.name' => $childName]);
        /** @psalm-var array<0, 1>|false $result */
        $result = $this
            ->getRowsCommand($parentName, baseOuterQuery: $baseOuterQuery, areParents: false)
            ->queryScalar();

        return $result !== false;
    }

    /**
     * @infection-ignore-all
     *  - ProtectedVisibility.
     *
     * @psalm-return non-empty-string
     */
    protected function getEmptyChildrenExpression(): string
    {
        return "''";
    }

    /**
     * @psalm-return non-empty-string
     */
    protected function getTrimConcatChildrenExpression(): string
    {
        return "TRIM(',' FROM CONCAT(children, ',', item_child_recursive.child))";
    }

    private function getRowsCommand(
        string|array $names,
        QueryInterface $baseOuterQuery,
        bool $areParents = true,
    ): CommandInterface {
        if ($areParents) {
            $cteSelectRelationName = 'parent';
            $cteConditionRelationName = 'child';
            $cteName = 'parent_of';
            $cteParameterName = 'child_name';
        } else {
            $cteSelectRelationName = 'child';
            $cteConditionRelationName = 'parent';
            $cteName = 'child_of';
            $cteParameterName = 'parent_name';
        }

        $cteSelectRelationQuery = (new Query($this->database))
            ->select($cteSelectRelationName)
            ->from(['item_child_recursive' => $this->childrenTableName])
            ->innerJoin($cteName, [
                "item_child_recursive.$cteConditionRelationName" => new Expression(
                    "{{{$cteName}}}.[[$cteParameterName]]",
                ),
            ]);
        $cteSelectItemQuery = (new Query($this->database))
            ->select('name')
            ->from($this->tableName)
            ->where(['name' => $names])
            ->union($cteSelectRelationQuery, all: true);
        $quoter = $this->database->getQuoter();
        $outerQuery = $baseOuterQuery
            ->withQuery(
                $cteSelectItemQuery,
                $quoter->quoteTableName($cteName) . '(' . $quoter->quoteColumnName($cteParameterName) . ')',
                recursive: $this->useRecursiveInWith,
            )
            ->from($cteName)
            ->leftJoin(
                ['item' => $this->tableName],
                ['item.name' => new Expression("{{{$cteName}}}.[[$cteParameterName]]")],
            );

        return $outerQuery->createCommand();
    }

    /**
     * @psalm-param string|non-empty-array<array-key, string> $names
     */
    private function getChildrenBaseOuterQuery(string|array $names): QueryInterface
    {
        $baseOuterQuery = (new Query($this->database))->select('item.*')->distinct();
        if (is_string($names)) {
            return $baseOuterQuery->where(['!=', 'item.name', $names]);
        }

        return $baseOuterQuery->where(['not in', 'item.name', $names]);
    }
}
