# Yii RBAC Database Storage Change Log

## 2.0.0 March 07, 2024

- Chg #23: Remove CLI dependencies (@arogachev)
- Chg #25: Use prefix for default table names (@arogachev)
- Chg #57: Raise PHP version to 8.1 (@arogachev)
- Chg #59: Add customizable separator for joining and splitting item names (@arogachev)
- Enh #23, #35, #60: Use migrations (@arogachev)
- Enh #26: Add default table names (@arogachev)
- Enh #35: Decouple storages: adjust database tables' schema (@arogachev)
- Enh #35: Add `TransactionlManageDecorator` for `Manager` to guarantee data integrity (@arogachev)
- Enh #43: Remove duplicate code in `ItemsStorage::add()` (@arogachev)
- Enh #45: Decrease size for string columns from 128 to 126 for PostgreSQL optimization (@arogachev)
- Enh #46, #64: Improve performance (@arogachev, @Tigrov)
- Enh #46, #68: Sync with base package (implement interface methods) (@arogachev)
- Enh #46: Rename `getChildren()` method to `getDirectAchildren()` in `ItemsStorage` (@arogachev)
- Enh #53: Use snake case for item attribute names (ease migration from Yii 2) (@arogachev)
- Bug #24: Remove usage of `SQLite` column in DB agnostic code (@arogachev)
- Bug #35: Implement `AssignmentStorage::renameItem()`, fix bug when implicit renaming had no effect (@arogachev)
- Bug #36: Fix hardcoded items table name in item tree traversal queries (@arogachev)
- Bug #44: Fix hardcoded items children table name in item tree traversal query for MySQL 5 (@arogachev)
- Bug #54: Fix ignoring of using `Assignment::$createdAt` in `AssignmentsStorage::add()` (@arogachev)

## 1.0.0 April 20, 2023

- Initial release.
