# Yii RBAC Database Storage Change Log

## 2.0.0 under development

- New #23: Remove CLI dependencies, add `DbSchemaManager`, dump SQL for working with schema (@arogachev) 
- Bug #24: Remove usage of `SQLite` column in DB agnostic code (@arogachev)
- Bug #36: Fix hardcoded items table name in item tree traversal queries (@arogachev)
- Enh #35: Decouple storages: adjust database tables' schema (@arogachev)
- Enh #35: Decouple storages: allow to manage tables just for 1 storage in `DbSchemaManager` (@arogachev)
- Enh #35: Add `TransactionlManageDecorator` for `Manager` to guarantee data integrity (@arogachev)
- Bug #35: Implement `AssignmentStorage::renameItem()`, fix bug when implicit renaming had no effect (@arogachev)

## 1.0.0 April 20, 2023

- Initial release.
