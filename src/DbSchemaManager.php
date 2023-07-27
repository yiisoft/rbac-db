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
     * @var string A name of the table for storing RBAC items (roles and permissions).
     * @psalm-var non-empty-string
     */
    private string $itemsTable;
    /**
     * @var string A name of the table for storing RBAC assignments.
     * @psalm-var non-empty-string
     */
    private string $assignmentsTable;
    /**
     * @var string A name of the table for storing relations between RBAC items.
     * @psalm-var non-empty-string
     */
    private string $itemsChildrenTable;

    /**
     * @param string $itemsTable A name of the table for storing RBAC items (roles and permissions).
     * @param string $assignmentsTable A name of the table for storing RBAC assignments.
     * @param ConnectionInterface $database Yii Database connection instance.
     * @param string|null $itemsChildrenTable A name of the table for storing relations between RBAC items. When set to
     * `null`, it will be automatically generated using {@see $itemsTable}.
     *
     * @throws InvalidArgumentException When a table name is set to the empty string.
     */
    public function __construct(
        string $itemsTable,
        string $assignmentsTable,
        private ConnectionInterface $database,
        string|null $itemsChildrenTable = null,
    ) {
        $this->initTables($itemsTable, $assignmentsTable, $itemsChildrenTable);
    }

    /**
     * Creates table for storing RBAC items (roles and permissions).
     *
     * @see $itemsTable
     */
    public function createItemsTable(): void
    {
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
                    "FOREIGN KEY ([[itemName]]) REFERENCES {{%$this->itemsTable}} ([[name]])",
                ],
            )
            ->execute();
    }

    /**
     * Checks existence of a table in {@see $database} by a given name
     *
     * @param string $tableName Table name for checking.
     *
     * @return bool Whether a table exists: `true` - exists, `false` - doesn't exist.
     */
    public function hasTable(string $tableName): bool
    {
        return $this->database->getSchema()->getTableSchema($tableName) !== null;
    }

    /**
     * Drops a table in {@see $database} by a given name.
     *
     * @param string $tableName Table name for dropping.
     */
    public function dropTable(string $tableName): void
    {
        $this->database->createCommand()->dropTable($tableName)->execute();
    }

    /**
     * Ensures all Yii RBAC related tables are present in the database. Creation is executed for each table only when it
     * doesn't exist.
     */
    public function ensureTables(): void
    {
        $result = $this->database->createCommand('SELECT * FROM ALL_TAB_COLUMNS A')->queryOne();
        var_dump($result);

        if (!$this->hasTable($this->itemsTable)) {
            $this->createItemsTable();
        }

        if (!$this->hasTable($this->itemsChildrenTable)) {
            $this->createItemsChildrenTable();
        }

        if (!$this->hasTable($this->assignmentsTable)) {
            $this->createAssignmentsTable();
        }
    }

    /**
     * Ensures no Yii RBAC related tables are present in the database. Drop is executed for each table only when it
     * exists.
     */
    public function ensureNoTables(): void
    {
        if ($this->hasTable($this->itemsChildrenTable)) {
            $this->dropTable($this->itemsChildrenTable);
        }

        if ($this->hasTable($this->assignmentsTable)) {
            $this->dropTable($this->assignmentsTable);
        }

        if ($this->hasTable($this->itemsTable)) {
            $this->dropTable($this->itemsTable);
        }
    }

    /**
     * Gets name of the table for storing RBAC items (roles and permissions).
     *
     * @return string Table name
     *
     * @see $itemsTable
     */
    public function getItemsTable(): string
    {
        return $this->itemsTable;
    }

    /**
     * Gets name of the table for storing RBAC assignments.
     *
     * @return string Table name.
     *
     * @see $assignmentsTable
     */
    public function getAssignmentsTable(): string
    {
        return $this->assignmentsTable;
    }

    /**
     * Gets name of the table for storing relations between RBAC items.
     *
     * @return string Table name
     *
     * @see $itemsChildrenTable
     */
    public function getItemsChildrenTable(): string
    {
        return $this->itemsChildrenTable;
    }

    /**
     * Initializes table names.
     *
     * @throws InvalidArgumentException When a table name is set to the empty string.
     */
    private function initTables(string $itemsTable, string $assignmentsTable, string|null $itemsChildrenTable): void
    {
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

        $this->itemsChildrenTable = $itemsChildrenTable ?? ($this->itemsTable . '_child');
    }
}
