<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\ItemTreeTraversal;

use RuntimeException;
use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * A factory for creating item tree traversal strategy depending on used RDBMS.
 *
 * @internal
 */
class ItemTreeTraversalFactory
{
    /**
     * Creates item tree traversal strategy depending on used RDBMS.
     *
     * @param ConnectionInterface $database Yii Database connection instance.
     *
     * @param string $tableName A name of the table for storing RBAC items.
     * @psalm-param non-empty-string $tableName
     *
     * @param string $childrenTableName A name of the table for storing relations between RBAC items.
     * @psalm-param non-empty-string $childrenTableName
     *
     * @param string $namesSeparator Separator used for joining item names.
     * @psalm-param non-empty-string $namesSeparator
     *
     * @throws RuntimeException When a database was configured with an unknown driver, either because it is not
     * supported by Yii Database out of the box or newly added by Yii Database and not supported / tested yet in this
     * package.
     * @return ItemTreeTraversalInterface Item tree traversal strategy.
     */
    public static function getItemTreeTraversal(
        ConnectionInterface $database,
        string $tableName,
        string $childrenTableName,
        string $namesSeparator,
    ): ItemTreeTraversalInterface {
        $arguments = [$database, $tableName, $childrenTableName, $namesSeparator];
        $driver = $database->getDriverName();

        // default - ignored due to the complexity of testing and preventing splitting of database argument.
        // @codeCoverageIgnoreStart
        return match ($driver) {
            'sqlite' => new SqliteCteItemTreeTraversal(...$arguments),
            'mysql' => self::getMysqlItemTreeTraversal($database, $tableName, $childrenTableName, $namesSeparator),
            'pgsql' => new PostgresCteItemTreeTraversal(...$arguments),
            'sqlsrv' => new MssqlCteItemTreeTraversal(...$arguments),
            'oci' => new OracleCteItemTreeTraversal(...$arguments),
            default => throw new RuntimeException("$driver database driver is not supported."),
        };
        // @codeCoverageIgnoreEnd
    }

    /**
     * Creates item tree traversal strategy for MySQL depending on its version.
     *
     * @param ConnectionInterface $database Yii Database connection instance.
     *
     * @param string $tableName A name of the table for storing RBAC items.
     * @psalm-param non-empty-string $tableName
     *
     * @param string $childrenTableName A name of the table for storing relations between RBAC items.
     * @psalm-param non-empty-string $childrenTableName
     *
     * @param string $namesSeparator Separator used for joining item names.
     * @psalm-param non-empty-string $namesSeparator
     *
     * @return MysqlCteItemTreeTraversal|MysqlItemTreeTraversal Item tree traversal strategy.
     */
    private static function getMysqlItemTreeTraversal(
        ConnectionInterface $database,
        string $tableName,
        string $childrenTableName,
        string $namesSeparator
    ): MysqlCteItemTreeTraversal|MysqlItemTreeTraversal {
        /** @psalm-var array{version: string} $row */
        $row = $database->createCommand('SELECT VERSION() AS version')->queryOne();
        $version = $row['version'];
        $arguments = [$database, $tableName, $childrenTableName, $namesSeparator];

        return str_starts_with($version, '5')
            ? new MysqlItemTreeTraversal(...$arguments)
            : new MysqlCteItemTreeTraversal(...$arguments);
    }
}
