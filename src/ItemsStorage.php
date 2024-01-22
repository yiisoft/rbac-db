<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db;

use InvalidArgumentException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Query\Query;
use Yiisoft\Rbac\Db\Exception\SeparatorCollisionException;
use Yiisoft\Rbac\Db\ItemTreeTraversal\ItemTreeTraversalFactory;
use Yiisoft\Rbac\Db\ItemTreeTraversal\ItemTreeTraversalInterface;
use Yiisoft\Rbac\Item;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

/**
 * **Warning:** Do not use directly! Use with `Manager` from {@link https://github.com/yiisoft/rbac} package.
 *
 * Storage for RBAC items (roles and permissions) and their relations in the form of database tables. Operations are
 * performed using Yii Database.
 *
 * @psalm-import-type ItemsIndexedByName from ItemsStorageInterface
 * @psalm-type RawItem = array{
 *     type: Item::TYPE_*,
 *     name: string,
 *     description: string|null,
 *     rule_name: string|null,
 *     created_at: int|string,
 *     updated_at: int|string
 * }
 * @psalm-type RawRole = array{
 *     type: Item::TYPE_ROLE,
 *     name: string,
 *     description: string|null,
 *     rule_name: string|null,
 *     created_at: int|string,
 *     updated_at: int|string
 *  }
 * @psalm-type RawPermission = array{
 *     type: Item::TYPE_PERMISSION,
 *     name: string,
 *     description: string|null,
 *     rule_name: string|null,
 *     created_at: int|string,
 *     updated_at: int|string
 * }
 */
final class ItemsStorage implements ItemsStorageInterface
{
    /**
     * @var string Separator used for joining and splitting item names.
     * @psalm-var non-empty-string
     */
    private string $namesSeparator = ',';
    /**
     * @var ItemTreeTraversalInterface|null Lazily created RBAC item tree traversal strategy.
     */
    private ?ItemTreeTraversalInterface $treeTraversal = null;

    /**
     * @param ConnectionInterface $database Yii database connection instance.
     *
     * @param string $tableName A name of the table for storing RBAC items.
     * @psalm-param non-empty-string $tableName
     *
     * @param string $childrenTableName A name of the table for storing relations between RBAC items. When set to
     * `null`, it will be automatically generated using {@see $tableName}.
     * @psalm-param non-empty-string $childrenTableName
     *
     * @param string $namesSeparator Separator used for joining and splitting item names.
     */
    public function __construct(
        private ConnectionInterface $database,
        private string $tableName = 'yii_rbac_item',
        private string $childrenTableName = 'yii_rbac_item_child',
        string $namesSeparator = ',',
    ) {
        $this->assertNamesSeparator($namesSeparator);
        $this->namesSeparator = $namesSeparator;
    }

    public function clear(): void
    {
        $itemsStorage = $this;
        $this->database->transaction(static function (ConnectionInterface $database) use ($itemsStorage): void {
            $database
                ->createCommand()
                ->delete($itemsStorage->childrenTableName)
                ->execute();
            $database
                ->createCommand()
                ->delete($itemsStorage->tableName)
                ->execute();
        });
    }

    public function getAll(): array
    {
        /** @psalm-var RawItem[] $rows */
        $rows = (new Query($this->database))
            ->from($this->tableName)
            ->all();

        return $this->getItemsIndexedByName($rows);
    }

    public function getByNames(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        /** @psalm-var RawItem[] $rawItems */
        $rawItems = (new Query($this->database))
            ->from($this->tableName)
            ->where(['name' => $names])
            ->all();

        return $this->getItemsIndexedByName($rawItems);
    }

    public function get(string $name): Permission|Role|null
    {
        /** @psalm-var RawItem|null $row */
        $row = (new Query($this->database))
            ->from($this->tableName)
            ->where(['name' => $name])
            ->one();

        return $row === null ? null : $this->createItem($row);
    }

    public function exists(string $name): bool
    {
        return (new Query($this->database))
            ->from($this->tableName)
            ->where(['name' => $name])
            ->exists();
    }

    public function roleExists(string $name): bool
    {
        return (new Query($this->database))
            ->from($this->tableName)
            ->where(['name' => $name, 'type' => Item::TYPE_ROLE])
            ->exists();
    }

    public function add(Item $item): void
    {
        $this
            ->database
            ->createCommand()
            ->insert(
                $this->tableName,
                $item->getAttributes(),
            )
            ->execute();
    }

    public function update(string $name, Item $item): void
    {
        $itemsStorage = $this;
        $this
            ->database
            ->transaction(static function (ConnectionInterface $database) use ($itemsStorage, $name, $item): void {
                $itemsChildren = (new Query($database))
                    ->from($itemsStorage->childrenTableName)
                    ->where(['parent' => $name])
                    ->orWhere(['child' => $name])
                    ->all();
                if ($itemsChildren !== []) {
                    $itemsStorage->removeRelatedItemsChildren($database, $name);
                }

                $database
                    ->createCommand()
                    ->update($itemsStorage->tableName, $item->getAttributes(), ['name' => $name])
                    ->execute();

                if ($itemsChildren !== []) {
                    $itemsChildren = array_map(
                        static function (array $itemChild) use ($name, $item): array {
                            if ($itemChild['parent'] === $name) {
                                $itemChild['parent'] = $item->getName();
                            }

                            if ($itemChild['child'] === $name) {
                                $itemChild['child'] = $item->getName();
                            }

                            return [$itemChild['parent'], $itemChild['child']];
                        },
                        $itemsChildren,
                    );
                    $database
                        ->createCommand()
                        ->batchInsert($itemsStorage->childrenTableName, ['parent', 'child'], $itemsChildren)
                        ->execute();
                }
            });
    }

    public function remove(string $name): void
    {
        $itemsStorage = $this;
        $this->database->transaction(static function (ConnectionInterface $database) use ($itemsStorage, $name): void {
            $itemsStorage->removeRelatedItemsChildren($database, $name);
            $database
                ->createCommand()
                ->delete($itemsStorage->tableName, ['name' => $name])
                ->execute();
        });
    }

    public function getRoles(): array
    {
        return $this->getItemsByType(Item::TYPE_ROLE);
    }

    public function getRolesByNames(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        /** @psalm-var RawRole[] $rawItems */
        $rawItems = (new Query($this->database))
            ->from($this->tableName)
            ->where(['type' => Item::TYPE_ROLE, 'name' => $names])
            ->all();

        /** @psalm-var array<string, Role> */
        return $this->getItemsIndexedByName($rawItems);
    }

    public function getRole(string $name): ?Role
    {
        return $this->getItemByTypeAndName(Item::TYPE_ROLE, $name);
    }

    public function clearRoles(): void
    {
        $this->clearItemsByType(Item::TYPE_ROLE);
    }

    public function getPermissions(): array
    {
        return $this->getItemsByType(Item::TYPE_PERMISSION);
    }

    public function getPermissionsByNames(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        /** @psalm-var RawPermission[] $rawItems */
        $rawItems = (new Query($this->database))
            ->from($this->tableName)
            ->where(['type' => Item::TYPE_PERMISSION, 'name' => $names])
            ->all();

        /** @psalm-var array<string, Permission> */
        return $this->getItemsIndexedByName($rawItems);
    }

    public function getPermission(string $name): ?Permission
    {
        return $this->getItemByTypeAndName(Item::TYPE_PERMISSION, $name);
    }

    public function clearPermissions(): void
    {
        $this->clearItemsByType(Item::TYPE_PERMISSION);
    }

    public function getParents(string $name): array
    {
        $rawItems = $this->getTreeTraversal()->getParentRows($name);

        return $this->getItemsIndexedByName($rawItems);
    }

    public function getAccessTree(string $name): array
    {
        $tree = [];
        $childrenNamesMap = [];

        foreach ($this->getTreeTraversal()->getAccessTree($name) as $data) {
            $childrenNamesMap[$data['name']] = $data['children'] !== '' && $data['children'] !== null
                ? explode($this->namesSeparator, $data['children'])
                : [];
            unset($data['children']);
            $tree[$data['name']] = ['item' => $this->createItem($data)];
        }

        foreach ($tree as $index => $_item) {
            $children = [];
            foreach ($childrenNamesMap[$index] as $childrenName) {
                if (!isset($tree[$childrenName])) {
                    throw new SeparatorCollisionException();
                }

                $children[$childrenName] = $tree[$childrenName]['item'];
            }

            $tree[$index]['children'] = $children;
        }

        return $tree;
    }

    public function getDirectChildren(string $name): array
    {
        $quoter = $this->database->getQuoter();
        $quotedJoinColumn = $quoter->quoteTableName($this->tableName) . '.' . $quoter->quoteColumnName('name');
        /** @psalm-var RawItem[] $rawItems */
        $rawItems = (new Query($this->database))
            ->select($this->tableName . '.*')
            ->from($this->tableName)
            ->leftJoin(
                $this->childrenTableName,
                [$this->childrenTableName . '.child' => new Expression($this->tableName . '.name')],
            )
            ->where(['parent' => $name])
            ->all();

        return $this->getItemsIndexedByName($rawItems);
    }

    public function getAllChildren(string|array $names): array
    {
        if (is_array($names) && empty($names)) {
            return [];
        }

        $rawItems = $this->getTreeTraversal()->getChildrenRows($names);

        return $this->getItemsIndexedByName($rawItems);
    }

    public function getAllChildPermissions(string|array $names): array
    {
        if (is_array($names) && empty($names)) {
            return [];
        }

        $rawItems = $this->getTreeTraversal()->getChildPermissionRows($names);

        /** @psalm-var array<string, Permission> */
        return $this->getItemsIndexedByName($rawItems);
    }

    public function getAllChildRoles(string|array $names): array
    {
        if (is_array($names) && empty($names)) {
            return [];
        }

        $rawItems = $this->getTreeTraversal()->getChildRoleRows($names);

        /** @psalm-var array<string, Role> */
        return $this->getItemsIndexedByName($rawItems);
    }

    public function hasChildren(string $name): bool
    {
        return (new Query($this->database))
            ->from($this->childrenTableName)
            ->where(['parent' => $name])
            ->exists();
    }

    public function hasChild(string $parentName, string $childName): bool
    {
        return $this->getTreeTraversal()->hasChild($parentName, $childName);
    }

    public function hasDirectChild(string $parentName, string $childName): bool
    {
        return (new Query($this->database))
            ->from($this->childrenTableName)
            ->where(['parent' => $parentName, 'child' => $childName])
            ->exists();
    }

    public function addChild(string $parentName, string $childName): void
    {
        $this
            ->database
            ->createCommand()
            ->insert(
                $this->childrenTableName,
                ['parent' => $parentName, 'child' => $childName]
            )
            ->execute();
    }

    public function removeChild(string $parentName, string $childName): void
    {
        $this
            ->database
            ->createCommand()
            ->delete($this->childrenTableName, ['parent' => $parentName, 'child' => $childName])
            ->execute();
    }

    public function removeChildren(string $parentName): void
    {
        $this
            ->database
            ->createCommand()
            ->delete($this->childrenTableName, ['parent' => $parentName])
            ->execute();
    }

    /**
     * Gets either all existing roles or permissions, depending on specified type.
     *
     * @param string $type Either {@see Item::TYPE_ROLE} or {@see Item::TYPE_PERMISSION}.
     * @psalm-param Item::TYPE_* $type
     *
     * @return array A list of roles / permissions.
     * @psalm-return ($type is Item::TYPE_PERMISSION ? array<string, Permission> : array<string, Role>)
     */
    private function getItemsByType(string $type): array
    {
        /** @psalm-var RawPermission[] | RawRole[] $rawItems */
        $rawItems = (new Query($this->database))
            ->from($this->tableName)
            ->where(['type' => $type])
            ->all();

        return $this->getItemsIndexedByName($rawItems);
    }

    /**
     * Gets single item by its type and name.
     *
     * @param string $type Either {@see Item::TYPE_ROLE} or {@see Item::TYPE_PERMISSION}.
     * @psalm-param Item::TYPE_* $type
     *
     * @return Permission|Role|null Either role or permission, depending on initial type specified. `null` is returned
     * when no item was found by given condition.
     * @psalm-return ($type is Item::TYPE_PERMISSION ? Permission : Role)|null
     */
    private function getItemByTypeAndName(string $type, string $name): Permission|Role|null
    {
        /**
         * @psalm-var RawItem|null $row
         * @infection-ignore-all
         * - ArrayItemRemoval, where, type.
         */
        $row = (new Query($this->database))
            ->from($this->tableName)
            ->where(['type' => $type, 'name' => $name])
            ->one();

        return $row === null ? null : $this->createItem($row);
    }

    /**
     * A factory method for creating single item with all attributes filled.
     *
     * @psalm-param RawPermission|RawRole $rawItem
     *
     * @return Permission|Role Either role or permission, depending on initial type specified.
     */
    private function createItem(array $rawItem): Permission|Role
    {
        $item = $this
            ->createItemByTypeAndName($rawItem['type'], $rawItem['name'])
            ->withDescription($rawItem['description'] ?? '')
            ->withRuleName($rawItem['rule_name'] ?? null)
            ->withCreatedAt((int) $rawItem['created_at'])
            ->withUpdatedAt((int) $rawItem['updated_at']);

        if ($rawItem['description'] !== null) {
            $item = $item->withDescription($rawItem['description']);
        }

        if ($rawItem['rule_name'] !== null) {
            $item = $item->withRuleName($rawItem['rule_name']);
        }

        return $item;
    }

    /**
     * A basic factory method for creating single item with name only.
     *
     * @param string $type Either {@see Item::TYPE_ROLE} or {@see Item::TYPE_PERMISSION}.
     * @psalm-param Item::TYPE_* $type
     *
     * @return Permission|Role Either role or permission, depending on initial type specified.
     * @psalm-return ($type is Item::TYPE_PERMISSION ? Permission : Role)
     */
    private function createItemByTypeAndName(string $type, string $name): Permission|Role
    {
        return $type === Item::TYPE_PERMISSION ? new Permission($name) : new Role($name);
    }

    /**
     * Removes all related records in items children table for a given item name.
     *
     * @param ConnectionInterface $database Yii database connection instance.
     * @param string $name Item name.
     */
    private function removeRelatedItemsChildren(ConnectionInterface $database, string $name): void
    {
        $database
            ->createCommand()
            ->delete($this->childrenTableName, ['or', ['parent' => $name], ['child' => $name]])
            ->execute();
    }

    /**
     * Removes all existing items of specified type.
     *
     * @param string $type Either {@see Item::TYPE_ROLE} or {@see Item::TYPE_PERMISSION}.
     * @psalm-param Item::TYPE_* $type
     */
    private function clearItemsByType(string $type): void
    {
        $itemsStorage = $this;
        $this->database->transaction(static function (ConnectionInterface $database) use ($itemsStorage, $type): void {
            $parentsSubQuery = (new Query($database))
                ->select('parents.parent')
                ->from([
                    'parents' => (new Query($database))
                        ->select('parent')
                        ->distinct()
                        ->from($itemsStorage->childrenTableName),
                ])
                ->leftJoin(
                    ['parent_items' => $itemsStorage->tableName],
                    '{{%parent_items}}.[[name]] = {{%parents}}.[[parent]]',
                )
                ->where(['parent_items.type' => $type]);
            $childrenSubQuery = (new Query($database))
                ->select('children.child')
                ->from([
                    'children' => (new Query($database))
                        ->select('child')
                        ->distinct()
                        ->from($itemsStorage->childrenTableName),
                ])
                ->leftJoin(
                    ['child_items' => $itemsStorage->tableName],
                    '{{%child_items}}.[[name]] = {{%children}}.[[child]]',
                )
                ->where(['child_items.type' => $type]);
            $database
                ->createCommand()
                ->delete(
                    $itemsStorage->childrenTableName,
                    ['or', ['parent' => $parentsSubQuery], ['child' => $childrenSubQuery]]
                )
                ->execute();
            $database
                ->createCommand()
                ->delete($itemsStorage->tableName, ['type' => $type])
                ->execute();
        });
    }

    /**
     * Creates RBAC item tree traversal strategy and returns it. In case it was already created, just retrieves
     * previously saved instance.
     */
    private function getTreeTraversal(): ItemTreeTraversalInterface
    {
        if ($this->treeTraversal === null) {
            $this->treeTraversal = ItemTreeTraversalFactory::getItemTreeTraversal(
                $this->database,
                $this->tableName,
                $this->childrenTableName,
                $this->namesSeparator,
            );
        }

        return $this->treeTraversal;
    }

    /**
     * @psalm-param RawItem[] $rawItems
     * @psalm-return ItemsIndexedByName
     */
    private function getItemsIndexedByName(array $rawItems): array
    {
        $items = [];

        foreach ($rawItems as $rawItem) {
            $items[$rawItem['name']] = $this->createItem($rawItem);
        }

        return $items;
    }

    /**
     * @psalm-assert non-empty-string $namesSeparator
     */
    private function assertNamesSeparator(string $namesSeparator): void
    {
        if (strlen($namesSeparator) !== 1) {
            throw new InvalidArgumentException('Names separator must be exactly 1 character long.');
        }
    }
}
