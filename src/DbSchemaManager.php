<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db;

use InvalidArgumentException;
use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * A class for working with RBAC tables' schema using configured Yii Database driver. Supports schema creation, deletion
 * and checking its existence.
 */
final class DbSchemaManager
{
    /**
     * @var string|null A name of the table for storing RBAC items (roles and permissions).
     * @psalm-var ?non-empty-string
     */
    private ?string $itemsTable;
    /**
     * @var string|null A name of the table for storing RBAC assignments.
     * @psalm-var ?non-empty-string
     */
    private ?string $assignmentsTable;
    /**
     * @var string|null A name of the table for storing relations between RBAC items.
     * @psalm-var ?non-empty-string
     */
    private ?string $itemsChildrenTable;

    /**
     * @param ConnectionInterface $database Yii Database connection instance.
     * @param string|null $itemsTable A name of the table for storing RBAC items (roles and permissions).
     * @param string|null $itemsChildrenTable A name of the table for storing relations between RBAC items. When set to
     * `null`, it will be automatically generated using {@see $itemsTable}.
     * @param string|null $assignmentsTable A name of the table for storing RBAC assignments.
     *
     * @throws InvalidArgumentException When a table name is set to the empty string.
     */
    public function __construct(
        private ConnectionInterface $database,
        ?string $itemsTable = null,
        ?string $itemsChildrenTable = null,
        ?string $assignmentsTable = null,
    ) {
        $this->initTables(
            itemsTable: $itemsTable,
            itemsChildrenTable: $itemsChildrenTable,
            assignmentsTable: $assignmentsTable,
        );
    }

    /**
     * Creates table for storing RBAC items (roles and permissions).
     *
     * @see $itemsTable
     */
    public function createItemsTable(): void
    {
        if ($this->itemsTable === null || $this->hasTable($this->itemsTable)) {
            return;
        }

        $this
            ->database
            ->createCommand()
            ->createTable(
                $this->itemsTable,
                [
                    'name' => 'string(128) NOT NULL PRIMARY KEY',
                    'type' => 'string(10) NOT NULL',
                    'description' => 'string(191)',
                    'ruleName' => 'string(64)',
                    'createdAt' => 'integer NOT NULL',
                    'updatedAt' => 'integer NOT NULL',
                ],
            )
            ->execute();
        $this
            ->database
            ->createCommand()
            ->createIndex($this->itemsTable, "idx-$this->itemsTable-type", 'type')
            ->execute();
    }

    /**
     * Creates table for storing relations between RBAC items.
     *
     * @see $itemsChildrenTable
     */
    public function createItemsChildrenTable(): void
    {
        if (
            $this->itemsTable ===null ||
            $this->itemsChildrenTable === null ||
            $this->hasTable($this->itemsChildrenTable)
        ) {
            return;
        }

        $this
            ->database
            ->createCommand()
            ->createTable(
                $this->itemsChildrenTable,
                [
                    'parent' => 'string(128) NOT NULL',
                    'child' => 'string(128) NOT NULL',
                    'PRIMARY KEY ([[parent]], [[child]])',
                    "FOREIGN KEY ([[parent]]) REFERENCES {{%$this->itemsTable}} ([[name]])",
                    "FOREIGN KEY ([[child]]) REFERENCES {{%$this->itemsTable}} ([[name]])",
                ],
            )
            ->execute();
    }

    /**
     * Creates table for storing RBAC assignments.
     *
     * @see $assignmentsTable
     */
    public function createAssignmentsTable(): void
    {
        if ($this->assignmentsTable === null || $this->hasTable($this->assignmentsTable)) {
            return;
        }

        $this
            ->database
            ->createCommand()
            ->createTable(
                $this->assignmentsTable,
                [
                    'itemName' => 'string(128) NOT NULL',
                    'userId' => 'string(128) NOT NULL',
                    'createdAt' => 'integer NOT NULL',
                    'PRIMARY KEY ([[itemName]], [[userId]])',
                ],
            )
            ->execute();
    }

    /**
     * Checks existence of a table in {@see $database} by a given name
     *
     * @param string $tableName Table name for checking.
     *
     * @throws InvalidArgumentException When a table name is set to the empty string.
     * @return bool Whether a table exists: `true` - exists, `false` - doesn't exist.
     */
    public function hasTable(string $tableName): bool
    {
        if ($tableName === '') {
            throw new InvalidArgumentException('Table name must be non-empty.');
        }

        return $this->database->getSchema()->getTableSchema($tableName) !== null;
    }

    /**
     * Drops a table in {@see $database} by a given name.
     *
     * @param string $tableName Table name for dropping.
     *
     * @throws InvalidArgumentException When a table name is set to the empty string.
     */
    public function dropTable(string $tableName): void
    {
        if ($tableName === '') {
            throw new InvalidArgumentException('Table name must be non-empty.');
        }

        $this->database->createCommand()->dropTable($tableName)->execute();
    }

    /**
     * Ensures all Yii RBAC related tables are present in the database. Creation is executed for each table only when it
     * doesn't exist.
     */
    public function ensureTables(): void
    {
        $this->createItemsTable();
        $this->createItemsChildrenTable();
        $this->createAssignmentsTable();
    }

    /**
     * Ensures no Yii RBAC related tables are present in the database. Drop is executed for each table only when it
     * exists.
     */
    public function ensureNoTables(): void
    {
        if ($this->itemsChildrenTable !== null && $this->hasTable($this->itemsChildrenTable)) {
            $this->dropTable($this->itemsChildrenTable);
        }

        if ($this->assignmentsTable !== null && $this->hasTable($this->assignmentsTable)) {
            $this->dropTable($this->assignmentsTable);
        }

        if ($this->itemsTable !== null && $this->hasTable($this->itemsTable)) {
            $this->dropTable($this->itemsTable);
        }
    }

    /**
     * Gets name of the table for storing RBAC items (roles and permissions).
     *
     * @return string|null Table name.
     *
     * @see $itemsTable
     */
    public function getItemsTable(): ?string
    {
        return $this->itemsTable;
    }

    /**
     * Gets name of the table for storing RBAC assignments.
     *
     * @return string|null Table name.
     *
     * @see $assignmentsTable
     */
    public function getAssignmentsTable(): ?string
    {
        return $this->assignmentsTable;
    }

    /**
     * Gets name of the table for storing relations between RBAC items.
     *
     * @return string|null Table name.
     *
     * @see $itemsChildrenTable
     */
    public function getItemsChildrenTable(): ?string
    {
        return $this->itemsChildrenTable;
    }

    /**
     * Initializes table names.
     *
     * @throws InvalidArgumentException When a table name is set to the empty string.
     */
    private function initTables(?string $itemsTable, ?string $itemsChildrenTable, ?string $assignmentsTable): void
    {
        if ($itemsTable === null && $assignmentsTable === null) {
            throw new InvalidArgumentException('At least items table or assignments table name must be set.');
        }

        if ($itemsTable === '') {
            throw new InvalidArgumentException('Items table name can\'t be empty.');
        }

        $this->itemsTable = $itemsTable;

        if ($assignmentsTable === '') {
            throw new InvalidArgumentException('Assignments table name can\'t be empty.');
        }

        $this->assignmentsTable = $assignmentsTable;

        if ($itemsChildrenTable === '') {
            throw new InvalidArgumentException('Items children table name can\'t be empty.');
        }

        $this->itemsChildrenTable = $itemsTable !== null && $itemsChildrenTable === null
            ? $itemsTable . '_child'
            : $itemsChildrenTable;
    }
}
