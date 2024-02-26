<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db;

use Closure;
use Stringable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

final class TransactionalManagerDecorator implements ManagerInterface
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly ConnectionInterface $database,
    ) {
    }

    public function userHasPermission(
        int|string|Stringable|null $userId,
        string $permissionName,
        array $parameters = [],
    ): bool {
        return $this->manager->userHasPermission($userId, $permissionName, $parameters);
    }

    public function canAddChild(string $parentName, string $childName): bool
    {
        return $this->manager->canAddChild($parentName, $childName);
    }

    public function addChild(string $parentName, string $childName): ManagerInterface
    {
        $this->manager->addChild($parentName, $childName);

        return $this;
    }

    public function removeChild(string $parentName, string $childName): ManagerInterface
    {
        $this->manager->removeChild($parentName, $childName);

        return $this;
    }

    public function removeChildren(string $parentName): ManagerInterface
    {
        $this->manager->removeChildren($parentName);

        return $this;
    }

    public function hasChild(string $parentName, string $childName): bool
    {
        return $this->manager->hasChild($parentName, $childName);
    }

    public function hasChildren(string $parentName): bool
    {
        return $this->manager->hasChildren($parentName);
    }

    public function assign(string $itemName, int|Stringable|string $userId, ?int $createdAt = null): ManagerInterface
    {
        $this->manager->assign($itemName, $userId, $createdAt);

        return $this;
    }

    public function revoke(string $itemName, int|Stringable|string $userId): ManagerInterface
    {
        $this->manager->revoke($itemName, $userId);

        return $this;
    }

    public function revokeAll(int|Stringable|string $userId): ManagerInterface
    {
        $this->manager->revokeAll($userId);

        return $this;
    }

    public function getItemsByUserId(int|Stringable|string $userId): array
    {
        return $this->manager->getItemsByUserId($userId);
    }

    public function getRolesByUserId(int|Stringable|string $userId): array
    {
        return $this->manager->getRolesByUserId($userId);
    }

    public function getChildRoles(string $roleName): array
    {
        return $this->manager->getChildRoles($roleName);
    }

    public function getPermissionsByRoleName(string $roleName): array
    {
        return $this->manager->getPermissionsByRoleName($roleName);
    }

    public function getPermissionsByUserId(int|Stringable|string $userId): array
    {
        return $this->manager->getPermissionsByUserId($userId);
    }

    public function getUserIdsByRoleName(string $roleName): array
    {
        return $this->manager->getUserIdsByRoleName($roleName);
    }

    public function addRole(Role $role): ManagerInterface
    {
        $this->manager->addRole($role);

        return $this;
    }

    public function getRole(string $name): ?Role
    {
        return $this->manager->getRole($name);
    }

    public function removeRole(string $name): ManagerInterface
    {
        $this->manager->removeRole($name);

        return $this;
    }

    public function updateRole(string $name, Role $role): ManagerInterface
    {
        $manager = $this->manager;
        $this->database->transaction(static fn () => $manager->updateRole($name, $role));

        return $this;
    }

    public function addPermission(Permission $permission): ManagerInterface
    {
        $this->manager->addPermission($permission);

        return $this;
    }

    public function getPermission(string $name): ?Permission
    {
        return $this->manager->getPermission($name);
    }

    public function removePermission(string $name): ManagerInterface
    {
        $this->manager->removePermission($name);

        return $this;
    }

    public function updatePermission(string $name, Permission $permission): ManagerInterface
    {
        $manager = $this->manager;
        $this->database->transaction(static fn () => $manager->updatePermission($name, $permission));

        return $this;
    }

    public function setDefaultRoleNames(Closure|array $roleNames): ManagerInterface
    {
        $this->manager->setDefaultRoleNames($roleNames);

        return $this;
    }

    public function getDefaultRoleNames(): array
    {
        return $this->manager->getDefaultRoleNames();
    }

    public function getDefaultRoles(): array
    {
        return $this->manager->getDefaultRoles();
    }

    public function setGuestRoleName(?string $name): ManagerInterface
    {
        $this->manager->setGuestRoleName($name);

        return $this;
    }

    public function getGuestRoleName(): ?string
    {
        return $this->manager->getGuestRoleName();
    }

    public function getGuestRole(): ?Role
    {
        return $this->manager->getGuestRole();
    }
}
