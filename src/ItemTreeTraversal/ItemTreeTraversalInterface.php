<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\ItemTreeTraversal;

use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\Item;

/**
 * An interface for retrieving hierarchical RBAC items' data in a more efficient way depending on used RDBMS and their
 * versions.
 *
 * @internal
 *
 * @psalm-type AccessTree = non-empty-list<array{
 *     type: Item::TYPE_*,
 *     name: string,
 *     description: string|null,
 *     ruleName: string|null,
 *     createdAt: int|string,
 *     updatedAt: int|string,
 *     children: string
 * }>
 *
 * @psalm-import-type RawItem from ItemsStorage
 */
interface ItemTreeTraversalInterface
{
    /**
     * Get all parent rows for an item by the given name.
     *
     * @param string $name Item name.
     *
     * @return array Flat list of all parents.
     * @psalm-return RawItem[]
     */
    public function getParentRows(string $name): array;

    /**
     * @psalm-return AccessTree
     */
    public function getAccessTree(string $name): array;

    /**
     * Get all children rows for an item by the given name.
     *
     * @param string|string[] $names Item name / names.
     * @psalm-param string|non-empty-array<array-key, string> $names
     *
     * @return array Flat list of all children.
     * @psalm-return RawItem[]
     */
    public function getChildrenRows(string|array $names): array;

    /**
     * Get all child permission rows for an item by the given name.
     *
     * @param string|string[] $names Item name / names.
     * @psalm-param string|non-empty-array<array-key, string> $names
     *
     * @return array Flat list of all child permissions.
     * @psalm-return RawItem[]
     */
    public function getChildPermissionRows(string|array $names): array;

    /**
     * Get all child role rows for an item by the given name.
     *
     * @param string|string[] $names Item name / names.
     * @psalm-param string|non-empty-array<array-key, string> $names
     *
     * @return array Flat list of all child roles.
     * @psalm-return RawItem[]
     */
    public function getChildRoleRows(string|array $names): array;

    /**
     * Whether a selected parent has specific child.
     *
     * @param string $parentName Parent item name.
     * @param string $childName Child item name.
     *
     * @return bool Whether a selected parent has specific child.
     */
    public function hasChild(string $parentName, string $childName): bool;
}
