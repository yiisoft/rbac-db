<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;
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
 * @psalm-type RawItem = array{
 *     type: Item::TYPE_*,
 *     name: string,
 *     description: string|null,
 *     ruleName: string|null,
 *     createdAt: int|string,
 *     updatedAt: int|string
 * }
 */
final class ItemsStorage implements ItemsStorageInterface
{
    /**
     * @psalm-var non-empty-string A name of the table for storing relations between RBAC items.
     */
    private string $childrenTableName;
    /**
     * @var ItemTreeTraversalInterface|null Lazily created RBAC item tree traversal strategy.
     */
    private ?ItemTreeTraversalInterface $treeTraversal = null;

    /**
     * @param string $tableName A name of the table for storing RBAC items.
     * @psalm-param non-empty-string $tableName
     *
     * @param ConnectionInterface $database Yii database connection instance.
     * @param string|null $childrenTableName A name of the table for storing relations between RBAC items. When set to
     * `null`, it will be automatically generated using {@see $tableName}.
     * @psalm-param non-empty-string|null $childrenTableName
     */
    public function __construct(
        private string $tableName,
        private ConnectionInterface $database,
        ?string $childrenTableName = null,
    ) {
        $this->childrenTableName = $childrenTableName ?? $tableName . '_child';
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

        return array_map(
            fn(array $row): Item => $this->createItem(...$row),
            $rows,
        );
    }

    public function get(string $name): ?Item
    {
        /** @psalm-var RawItem|null $row */
        $row = (new Query($this->database))
            ->from($this->tableName)
            ->where(['name' => $name])
            ->one();

        return $row === null ? null : $this->createItem(...$row);
    }

    public function exists(string $name): bool
    {
        return (new Query($this->database))
            ->from($this->tableName)
            ->where(['name' => $name])
            ->exists();
    }

    public function add(Item $item): void
    {
        $time = time();

        if (!$item->hasCreatedAt()) {
            $item = $item->withCreatedAt($time);
        }

        if (!$item->hasUpdatedAt()) {
            $item = $item->withUpdatedAt($time);
        }

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
        $items = [];

        foreach ($rawItems as $rawItem) {
            $items[$rawItem['name']] = $this->createItem(...$rawItem);
        }

        return $items;
    }

    public function getChildren(string $name): array
    {
        $rawItems = $this->getTreeTraversal()->getChildrenRows($name);
        $items = [];

        foreach ($rawItems as $rawItem) {
            $items[$rawItem['name']] = $this->createItem(...$rawItem);
        }

        return $items;
    }

    public function hasChildren(string $name): bool
    {
        return (new Query($this->database))
            ->from($this->childrenTableName)
            ->where(['parent' => $name])
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
     * @psalm-return ($type is Item::TYPE_PERMISSION ? Permission[] : Role[])
     */
    private function getItemsByType(string $type): array
    {
        /** @psalm-var RawItem[] $rows */
        $rows = (new Query($this->database))
            ->from($this->tableName)
            ->where(['type' => $type])
            ->all();

        return array_map(
            fn(array $row): Item => $this->createItem(...$row),
            $rows,
        );
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

        return $row === null ? null : $this->createItem(...$row);
    }

    /**
     * A factory method for creating single item with all attributes filled.
     *
     * @param string $type Either {@see Item::TYPE_ROLE} or {@see Item::TYPE_PERMISSION}.
     * @psalm-param Item::TYPE_* $type
     *
     * @param string $name Unique name.
     * @param int|string $createdAt UNIX timestamp for creation time.
     * @param int|string $updatedAt UNIX timestamp for updating time.
     * @param string|null $description Optional description.
     * @param string|null $ruleName Optional associated rule name.
     *
     * @return Permission|Role Either role or permission, depending on initial type specified.
     * @psalm-return ($type is Item::TYPE_PERMISSION ? Permission : Role)
     */
    private function createItem(
        string $type,
        string $name,
        int|string $createdAt,
        int|string $updatedAt,
        string|null $description = null,
        string|null $ruleName = null,
    ): Permission|Role {
        return $this
            ->createItemByTypeAndName($type, $name)
            ->withDescription($description ?? '')
            ->withRuleName($ruleName ?? null)
            ->withCreatedAt((int) $createdAt)
            ->withUpdatedAt((int) $updatedAt);
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
                ->leftJoin(['parent_items' => $itemsStorage->tableName], 'parent_items.name = parents.parent')
                ->where(['parent_items.type' => $type]);
            $childrenSubQuery = (new Query($database))
                ->select('children.child')
                ->from([
                    'children' => (new Query($database))
                        ->select('child')
                        ->distinct()
                        ->from($itemsStorage->childrenTableName),
                ])
                ->leftJoin(['child_items' => $itemsStorage->tableName], 'child_items.name = children.child')
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
            );
        }

        return $this->treeTraversal;
    }
}
