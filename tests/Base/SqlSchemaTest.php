<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Yiisoft\Db\Connection\ConnectionInterface;

abstract class SqlSchemaTest extends TestCase
{
    use SchemaTrait;

    protected static string $driverName = '';
    protected static array $upQueries = [];
    protected static array $downQueries = [];

    public static function setUpBeforeClass(): void
    {
        $driverName = static::$driverName;
        $sqlBasePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'sql';

        self::$upQueries = self::parseQueries($sqlBasePath . DIRECTORY_SEPARATOR . "$driverName-up.sql");
        self::$downQueries = self::parseQueries($sqlBasePath . DIRECTORY_SEPARATOR . "$driverName-down.sql");
    }

    protected function setUp(): void
    {
        // Skip
    }

    protected function populateDatabase(): void
    {
        // Skip
    }

    protected function createSchema(): void
    {
        $this->getDatabase()->transaction(static function (ConnectionInterface $database): void {
            foreach (self::$upQueries as $query) {
                $database->createCommand($query)->execute();
            }
        });
    }

    protected function dropSchema(): void
    {
        $this->getDatabase()->transaction(static function (ConnectionInterface $database): void {
            foreach (self::$downQueries as $query) {
                $database->createCommand($query)->execute();
            }
        });
    }

    public function testCreateSchema(): void
    {
        $this->createSchema();
        $this->checkTables();
    }

    public function testDropSchema(): void
    {
        $this->createSchemaManager()->ensureTables();
        $this->dropSchema();
        $this->checkNoTables();
    }

    protected static function parseQueries(string $sqlPath): array
    {
        $sql = file_get_contents($sqlPath);
        $sql = trim($sql);
        $sql = rtrim($sql, ';');

        return preg_split('/;\R/', $sql);
    }
}
