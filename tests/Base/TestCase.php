<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Migration\Command\DownCommand;
use Yiisoft\Db\Migration\Command\UpdateCommand;
use Yiisoft\Db\Migration\Informer\NullMigrationInformer;
use Yiisoft\Db\Migration\Migrator;
use Yiisoft\Db\Migration\Runner\DownRunner;
use Yiisoft\Db\Migration\Runner\UpdateRunner;
use Yiisoft\Db\Migration\Service\MigrationService;
use Yiisoft\Injector\Injector;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected static string $itemsTable = 'yii_rbac_item';
    protected static string $itemsChildrenTable = 'yii_rbac_item_child';
    protected static string $assignmentsTable = 'yii_rbac_assignment';
    protected static array $migrationsSubfolders = ['items', 'assignments'];

    protected static ?ConnectionInterface $database = null;
    private ?DownCommand $migrateDownCommand = null;
    private ?UpdateCommand $migrateUpdateCommand = null;

    protected function getDatabase(): ConnectionInterface
    {
        if (self::$database === null) {
            self::$database = $this->makeDatabase();
        }

        return self::$database;
    }

    protected function getMigrateUpdateCommand(): UpdateCommand
    {
        if ($this->migrateUpdateCommand !== null) {
            return $this->migrateUpdateCommand;
        }

        $migrator = new Migrator($this->getDatabase(), new NullMigrationInformer());
        $migrationService = new MigrationService($this->getDatabase(), new Injector(), $migrator);

        $directories = [];
        foreach (static::$migrationsSubfolders as $subfolder) {
            $directories[] = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'migrations', $subfolder]);
        }

        $migrationService->setSourcePaths($directories);

        $this->migrateUpdateCommand = new UpdateCommand(new UpdateRunner($migrator), $migrationService, $migrator);
        $this->migrateUpdateCommand->setHelperSet(new HelperSet([
            'queestion' => new QuestionHelper(),
        ]));

        return $this->migrateUpdateCommand;
    }

    protected function getMigrateDownCommand(): DownCommand
    {
        if ($this->migrateDownCommand !== null) {
            return $this->migrateDownCommand;
        }

        $migrator = new Migrator($this->getDatabase(), new NullMigrationInformer());
        $migrationService = new MigrationService($this->getDatabase(), new Injector(), $migrator);

        $directories = [];
        foreach (static::$migrationsSubfolders as $subfolder) {
            $directories[] = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'migrations', $subfolder]);
        }

        $migrationService->setSourcePaths($directories);

        $this->migrateDownCommand = new DownCommand(new DownRunner($migrator), $migrationService, $migrator);
        $this->migrateDownCommand->setHelperSet(new HelperSet([
            'queestion' => new QuestionHelper(),
        ]));

        return $this->migrateDownCommand;
    }

    public static function setUpBeforeClass(): void
    {
        (new static(static::class))->runMigrations();
    }

    public static function tearDownAfterClass(): void
    {
        (new static(static::class))->rollbackMigrations();
    }

    protected function setUp(): void
    {
        $this->populateDatabase();
    }

    protected function tearDown(): void
    {
        if ($this->getDatabase()->getDriverName() === 'sqlsrv') {
            $this->getDatabase()->close();
        }
    }

    protected function runMigrations(): void
    {
        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $this->getMigrateUpdateCommand()->run($input, new NullOutput());
    }

    protected function rollbackMigrations(): void
    {
        $input = new ArrayInput(['--all' => true]);
        $input->setInteractive(false);

        $this->getMigrateDownCommand()->run($input, new NullOutput());
    }

    abstract protected function makeDatabase(): ConnectionInterface;

    abstract protected function populateDatabase(): void;
}
