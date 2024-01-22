<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;
use Yiisoft\Rbac\Assignment;
use Yiisoft\Rbac\AssignmentsStorageInterface;

/**
 * **Warning:** Do not use directly! Use with `Manager` from {@link https://github.com/yiisoft/rbac} package.
 *
 * Storage for RBAC assignments in the form of database table. Operations are performed using Yii Database.
 *
 * @psalm-type RawAssignment = array{
 *     item_name: string,
 *     user_id: string,
 *     created_at: int|string,
 * }
 */
final class AssignmentsStorage implements AssignmentsStorageInterface
{
    /**
     * @param ConnectionInterface $database Yii Database connection instance.
     *
     * @param string $tableName A name of the table for storing RBAC assignments.
     * @psalm-param non-empty-string $tableName
     */
    public function __construct(
        private ConnectionInterface $database,
        private string $tableName = 'yii_rbac_assignment',
    ) {
    }

    public function getAll(): array
    {
        /** @psalm-var RawAssignment[] $rows */
        $rows = (new Query($this->database))
            ->from($this->tableName)
            ->all();

        $assignments = [];
        foreach ($rows as $row) {
            $assignments[$row['user_id']][$row['item_name']] = new Assignment(
                $row['user_id'],
                $row['item_name'],
                (int) $row['created_at'],
            );
        }

        return $assignments;
    }

    public function getByUserId(string $userId): array
    {
        /** @psalm-var list<array{item_name: string, created_at: int|string}> $rawAssignments */
        $rawAssignments = (new Query($this->database))
            ->select(['item_name', 'created_at'])
            ->from($this->tableName)
            ->where(['user_id' => $userId])
            ->all();
        $assignments = [];
        foreach ($rawAssignments as $rawAssignment) {
            $assignments[$rawAssignment['item_name']] = new Assignment(
                $userId,
                $rawAssignment['item_name'],
                (int) $rawAssignment['created_at'],
            );
        }

        return $assignments;
    }

    public function getByItemNames(array $itemNames): array
    {
        if (empty($itemNames)) {
            return [];
        }

        /** @psalm-var RawAssignment[] $rawAssignments */
        $rawAssignments = (new Query($this->database))
            ->from($this->tableName)
            ->where(['item_name' => $itemNames])
            ->all();
        $assignments = [];
        foreach ($rawAssignments as $rawAssignment) {
            $assignments[] = new Assignment(
                $rawAssignment['user_id'],
                $rawAssignment['item_name'],
                (int) $rawAssignment['created_at'],
            );
        }

        return $assignments;
    }

    public function get(string $itemName, string $userId): ?Assignment
    {
        /**
         * @psalm-var RawAssignment|null $row
         * @infection-ignore-all
         * - ArrayItemRemoval, select.
         */
        $row = (new Query($this->database))
            ->select(['created_at'])
            ->from($this->tableName)
            ->where(['item_name' => $itemName, 'user_id' => $userId])
            ->one();

        return $row === null ? null : new Assignment($userId, $itemName, (int) $row['created_at']);
    }

    public function exists(string $itemName, string $userId): bool
    {
        return (new Query($this->database))
            ->from($this->tableName)
            ->where(['item_name' => $itemName, 'user_id' => $userId])
            ->exists();
    }

    public function userHasItem(string $userId, array $itemNames): bool
    {
        if (empty($itemNames)) {
            return false;
        }

        return (new Query($this->database))
            ->from($this->tableName)
            ->where(['user_id' => $userId, 'item_name' => $itemNames])
            ->exists();
    }

    public function filterUserItemNames(string $userId, array $itemNames): array
    {
        /** @var array{item_name: string} $rows */
        $rows = (new Query($this->database))
            ->select('item_name')
            ->from($this->tableName)
            ->where(['user_id' => $userId, 'item_name' => $itemNames])
            ->all();

        return array_column($rows, 'item_name');
    }

    public function add(Assignment $assignment): void
    {
        $this
            ->database
            ->createCommand()
            ->insert(
                $this->tableName,
                [
                    'item_name' => $assignment->getItemName(),
                    'user_id' => $assignment->getUserId(),
                    'created_at' => $assignment->getCreatedAt(),
                ],
            )
            ->execute();
    }

    public function hasItem(string $name): bool
    {
        return (new Query($this->database))
            ->from($this->tableName)
            ->where(['item_name' => $name])
            ->exists();
    }

    public function renameItem(string $oldName, string $newName): void
    {
        $this
            ->database
            ->createCommand()
            ->update($this->tableName, columns: ['item_name' => $newName], condition: ['item_name' => $oldName])
            ->execute();
    }

    public function remove(string $itemName, string $userId): void
    {
        $this
            ->database
            ->createCommand()
            ->delete($this->tableName, ['item_name' => $itemName, 'user_id' => $userId])
            ->execute();
    }

    public function removeByUserId(string $userId): void
    {
        $this
            ->database
            ->createCommand()
            ->delete($this->tableName, ['user_id' => $userId])
            ->execute();
    }

    public function removeByItemName(string $itemName): void
    {
        $this
            ->database
            ->createCommand()
            ->delete($this->tableName, ['item_name' => $itemName])
            ->execute();
    }

    public function clear(): void
    {
        $this
            ->database
            ->createCommand()
            ->delete($this->tableName)
            ->execute();
    }
}
