<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db;

use InvalidArgumentException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Schema\SchemaInterface;
use Yiisoft\Db\Sqlite\Column;
use Yiisoft\Rbac\SchemaManagerInterface;
use Yiisoft\Rbac\SchemaManagerTrait;

final class SchemaManager implements SchemaManagerInterface
{
    use SchemaManagerTrait;

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
                    'name' => (new Column(SchemaInterface::TYPE_STRING, 128))->notNull()->append('PRIMARY KEY'),
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

    public function hasTable(string $tableName): bool
    {
        return $this->database->getSchema()->getTableSchema($tableName) !== null;
    }

    public function dropTable(string $tableName): void
    {
        $this->database->createCommand()->dropTable($tableName)->execute();
    }
}
