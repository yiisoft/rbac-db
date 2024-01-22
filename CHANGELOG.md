# Yii RBAC Database Storage Change Log

## 2.0.0 under development

- New #23: Remove CLI dependencies, add `DbSchemaManager`, dump SQL for working with schema (@arogachev) 
- Bug #24: Remove usage of `SQLite` column in DB agnostic code (@arogachev)
- Bug #36: Fix hardcoded items table name in item tree traversal queries (@arogachev)
- Enh #35: Decouple storages: adjust database tables' schema (@arogachev)
- Enh #35: Decouple storages: allow to manage tables just for 1 storage in `DbSchemaManager` (@arogachev)
- Enh #35: Add `TransactionlManageDecorator` for `Manager` to guarantee data integrity (@arogachev)
- Bug #35: Implement `AssignmentStorage::renameItem()`, fix bug when implicit renaming had no effect (@arogachev)
- Enh #26: Add default table names (@arogachev)
- Chg #25: Use prefix for default table names (@arogachev)
- Bug #44: Fix hardcoded items children table name in item tree traversal query for MySQL 5 (@arogachev)
- Enh #46: Improve performance (@arogachev, @Tigrov)
- Enh #46: Rename `getChildren()` method to `getDirectAchildren()` in `ItemsStorage` (@arogachev)
- Enh #46: Add methods to `ItemsStorage`:
    - `roleExists()`;
    - `getRolesByNames()`;
    - `getPermissionsByNames()`;
    - `getAllChildren()`;
    - `getAllChildRoles()`;
    - `getAllChildPermissions()`;
    - `hasChild()`;
    - `hasDirectChild()`.
      (@arogachev)
- Enh #46: Add methods to `AssignmentsStorage`:
    - `getByItemNames()`;
    - `exists()`;
    - `userHasItem()`.
      (@arogachev)
- Bug #54: Fix ignoring of using `Assignment::$createdAt` in `AssignmentsStorage::add()` (@arogachev)
- Chg #57: Raise PHP version to 8.1 (@arogachev)
- Chg #59: Add customizable separator for joining and splitting item names (@arogachev)
- Enh #60: Use migrations (@arogachev)
- Enh #43: Remove duplicate code in `ItemsStorage::add()` (@arogachev)
- Enh #53: Use snake case for item attribute names (ease migration from Yii 2) (@arogachev)c

## 1.0.0 April 20, 2023

- Initial release.
