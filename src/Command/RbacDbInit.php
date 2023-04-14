<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Schema\SchemaInterface;
use Yiisoft\Db\Sqlite\Column;

/**
 * Command for creating RBAC related database tables using Yii Database.
 */
final class RbacDbInit extends Command
{
    protected static $defaultName = 'rbac/db/init';

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

        $this->itemsChildrenTable = $itemsChildrenTable ?? $this->itemsTable . '_child';

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create RBAC schemas')
            ->setHelp('This command creates schemas for RBAC using Yii Database')
            ->addOption(name: 'force', shortcut: 'f', description: 'Force recreation of schemas if they exist');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var bool $force */
        $force = $input->getOption('force');
        if ($force === true) {
            $this->dropTable($this->itemsChildrenTable, $output);
            $this->dropTable($this->assignmentsTable, $output);
            $this->dropTable($this->itemsTable, $output);
        }

        $this->createTable($this->itemsTable, $output);
        $this->createTable($this->itemsChildrenTable, $output);
        $this->createTable($this->assignmentsTable, $output);

        $output->writeln('<fg=green>DONE</>');

        return Command::SUCCESS;
    }

    /**
     * Creates table for storing RBAC items (roles and permissions).
     *
     * @see $itemsTable
     */
    private function createItemsTable(): void
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
    private function createItemsChildrenTable(): void
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
                    'FOREIGN KEY ([[parent]]) REFERENCES {{%' . $this->itemsTable . '}} ([[name]])',
                    'FOREIGN KEY ([[child]]) REFERENCES {{%' . $this->itemsTable . '}} ([[name]])',
                ],
            )
            ->execute();
    }

    /**
     * Creates table for storing RBAC assignments.
     *
     * @see $assignmentsTable
     */
    private function createAssignmentsTable(): void
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
                    'FOREIGN KEY ([[itemName]]) REFERENCES {{%' . $this->itemsTable . '}} ([[name]])',
                ],
            )
            ->execute();
    }

    /**
     * Basic method for creating RBAC related table. When a table already exists, creation is skipped. Operations are
     * accompanied by explanations printed to console.
     *
     * @param string $tableName A name of created table.
     * @psalm-param non-empty-string $tableName
     *
     * @param OutputInterface $output Output for writing messages.
     */
    private function createTable(string $tableName, OutputInterface $output): void
    {
        $output->writeln("<fg=blue>Checking existence of `$tableName` table...</>");

        if ($this->database->getSchema()->getTableSchema($tableName) !== null) {
            $output->writeln("<bg=yellow>`$tableName` table already exists. Skipped creating.</>");

            return;
        }

        $output->writeln("<fg=blue>`$tableName` table doesn't exist. Creating...</>");

        match ($tableName) {
            $this->itemsTable => $this->createItemsTable(),
            $this->assignmentsTable => $this->createAssignmentsTable(),
            $this->itemsChildrenTable => $this->createItemsChildrenTable(),
        };

        $output->writeln("<bg=green>`$tableName` table has been successfully created.</>");
    }

    /**
     * Basic method for dropping RBAC related table. When a table already exists, dropping is skipped. Operations are
     * accompanied by explanations printed to console.
     *
     * @param string $tableName A name of created table.
     * @psalm-param non-empty-string $tableName
     *
     * @param OutputInterface $output Output for writing messages.
     */
    private function dropTable(string $tableName, OutputInterface $output): void
    {
        $output->writeln("<fg=blue>Checking existence of `$tableName` table...</>");

        if ($this->database->getSchema()->getTableSchema($tableName) === null) {
            $output->writeln("<bg=yellow>`$tableName` table doesn't exist. Skipped dropping.</>");

            return;
        }

        $output->writeln("<fg=blue>`$tableName` table exists. Dropping...</>");

        $this->database->createCommand()->dropTable($tableName)->execute();

        $output->writeln("<bg=green>`$tableName` table has been successfully dropped.</>");
    }
}
