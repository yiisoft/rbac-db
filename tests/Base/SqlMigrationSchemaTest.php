<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use DirectoryIterator;

abstract class SqlMigrationSchemaTest extends SqlSchemaTest
{
    public static function setUpBeforeClass(): void
    {
        $driverName = static::$driverName;
        $migrationsFolderPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'migrations';
        $migrationsFolderIterator = new DirectoryIterator($migrationsFolderPath);
        self::$upQueries = [];
        self::$downQueries = [];
        foreach ($migrationsFolderIterator as $migrationFolder) {
            if (!$migrationFolder->isDir() || $migrationFolder->isDot()) {
                continue;
            }

            $sqlBasePath = $migrationsFolderPath . DIRECTORY_SEPARATOR . $migrationFolder->getFilename();

            $upSqlPath = $sqlBasePath . DIRECTORY_SEPARATOR . "$driverName-up.sql";
            self::$upQueries = array_merge(self::$upQueries, self::parseQueries($upSqlPath));

            $downSqlPath = $sqlBasePath . DIRECTORY_SEPARATOR . "$driverName-down.sql";
            self::$downQueries = array_merge(self::$downQueries, self::parseQueries($downSqlPath));
        }
    }
}
