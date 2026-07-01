<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Sqlite;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Migration\Command\DownCommand;
use Yiisoft\Db\Migration\Command\UpdateCommand;
use Yiisoft\Db\Migration\Informer\NullMigrationInformer;
use Yiisoft\Db\Migration\Migrator;
use Yiisoft\Db\Migration\Runner\DownRunner;
use Yiisoft\Db\Migration\Runner\UpdateRunner;
use Yiisoft\Db\Migration\Service\MigrationService;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;
use Yiisoft\Injector\Injector;

use function dirname;

final class SchemaWithTablePrefixTest extends TestCase
{
    private const TABLE_PREFIX = 'test_';

    public function testMigrationsRespectTablePrefix(): void
    {
        $db = $this->makeDatabase();

        $this->runMigrations($db);

        $schema = $db->getSchema();
        $this->assertNotNull($schema->getTableSchema(self::TABLE_PREFIX . 'yii_rbac_item'));
        $this->assertNotNull($schema->getTableSchema(self::TABLE_PREFIX . 'yii_rbac_item_child'));
        $this->assertNotNull($schema->getTableSchema(self::TABLE_PREFIX . 'yii_rbac_assignment'));
        $this->assertNull($schema->getTableSchema('yii_rbac_item'));
        $this->assertNull($schema->getTableSchema('yii_rbac_item_child'));
        $this->assertNull($schema->getTableSchema('yii_rbac_assignment'));

        $this->rollbackMigrations($db);

        $this->assertNull($schema->getTableSchema(self::TABLE_PREFIX . 'yii_rbac_item', true));
        $this->assertNull($schema->getTableSchema(self::TABLE_PREFIX . 'yii_rbac_item_child', true));
        $this->assertNull($schema->getTableSchema(self::TABLE_PREFIX . 'yii_rbac_assignment', true));
    }

    private function makeDatabase(): ConnectionInterface
    {
        $pdoDriver = new Driver(dsn: 'sqlite::memory:');
        $pdoDriver->charset('UTF8MB4');

        $connection = new Connection($pdoDriver, new SchemaCache(new ArrayCache()));
        $connection->createCommand('PRAGMA foreign_keys = ON;')->execute();
        $connection->setTablePrefix(self::TABLE_PREFIX);

        return $connection;
    }

    private function runMigrations(ConnectionInterface $db): void
    {
        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $this->createMigrateUpdateCommand($db)->run($input, new NullOutput());
    }

    private function rollbackMigrations(ConnectionInterface $db): void
    {
        $input = new ArrayInput(['--all' => true]);
        $input->setInteractive(false);

        $this->createMigrateDownCommand($db)->run($input, new NullOutput());
    }

    private function createMigrateUpdateCommand(ConnectionInterface $db): UpdateCommand
    {
        $migrator = new Migrator($db, new NullMigrationInformer());
        $migrationService = new MigrationService($db, new Injector(), $migrator);
        $migrationService->setSourcePaths($this->getMigrationPaths());

        $command = new UpdateCommand(new UpdateRunner($migrator), $migrationService, $migrator);
        $command->setHelperSet(new HelperSet(['question' => new QuestionHelper()]));

        return $command;
    }

    private function createMigrateDownCommand(ConnectionInterface $db): DownCommand
    {
        $migrator = new Migrator($db, new NullMigrationInformer());
        $migrationService = new MigrationService($db, new Injector(), $migrator);
        $migrationService->setSourcePaths($this->getMigrationPaths());

        $command = new DownCommand(new DownRunner($migrator), $migrationService, $migrator);
        $command->setHelperSet(new HelperSet(['question' => new QuestionHelper()]));

        return $command;
    }

    private function getMigrationPaths(): array
    {
        $migrationsDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'migrations';

        return [
            $migrationsDir . DIRECTORY_SEPARATOR . 'items',
            $migrationsDir . DIRECTORY_SEPARATOR . 'assignments',
        ];
    }
}
