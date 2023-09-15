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
 *     itemName: string,
 *     userId: string,
 *     createdAt: int|string,
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
        private string $tableName = DbSchemaManager::ASSIGNMENTS_TABLE,
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
            $assignments[$row['userId']][$row['itemName']] = new Assignment(
                $row['userId'],
                $row['itemName'],
                (int) $row['createdAt'],
            );
        }

        return $assignments;
    }

    public function getByUserId(string $userId): array
    {
        /** @psalm-var RawAssignment[] $rows */
        $rows = (new Query($this->database))
            ->from($this->tableName)
            ->where(['userId' => $userId])
            ->all();

        return array_combine(
            array_column($rows, 'itemName'),
            array_map(
                static fn(array $row): Assignment => new Assignment($userId, $row['itemName'], (int) $row['createdAt']),
                $rows,
            )
        );
    }

    public function get(string $itemName, string $userId): ?Assignment
    {
        /** @psalm-var RawAssignment|null $row */
        $row = (new Query($this->database))
            ->from($this->tableName)
            ->where(['itemName' => $itemName, 'userId' => $userId])
            ->one();

        return $row === null ? null : new Assignment($row['userId'], $row['itemName'], (int) $row['createdAt']);
    }

    public function add(string $itemName, string $userId): void
    {
        $this
            ->database
            ->createCommand()
            ->insert(
                $this->tableName,
                [
                    'itemName' => $itemName,
                    'userId' => $userId,
                    'createdAt' => time(),
                ],
            )
            ->execute();
    }

    public function hasItem(string $name): bool
    {
        return (new Query($this->database))
            ->from($this->tableName)
            ->where(['itemName' => $name])
            ->exists();
    }

    public function renameItem(string $oldName, string $newName): void
    {
        $this
            ->database
            ->createCommand()
            ->update($this->tableName, columns: ['itemName' => $newName], condition: ['itemName' => $oldName])
            ->execute();
    }

    public function remove(string $itemName, string $userId): void
    {
        $this
            ->database
            ->createCommand()
            ->delete($this->tableName, ['itemName' => $itemName, 'userId' => $userId])
            ->execute();
    }

    public function removeByUserId(string $userId): void
    {
        $this
            ->database
            ->createCommand()
            ->delete($this->tableName, ['userId' => $userId])
            ->execute();
    }

    public function removeByItemName(string $itemName): void
    {
        $this
            ->database
            ->createCommand()
            ->delete($this->tableName, ['itemName' => $itemName])
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
