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
     * @throws RuntimeException When a database was configured with unknown driver, either not supported by Yii Database
     * out of the box or newly added by Yii Database and not supported / tested yet in this package.
     * @return ItemTreeTraversalInterface Item tree traversal strategy.
     */
    public static function getItemTreeTraversal(
        ConnectionInterface $database,
        string $tableName,
        string $childrenTableName,
    ): ItemTreeTraversalInterface {
        $arguments = [$database, $tableName, $childrenTableName];
        $driver = $database->getDriverName();

        return new SqliteCteItemTreeTraversal(...$arguments);

//        if ($driver instanceof SQLiteDriver) {
//            return new SqliteCteItemTreeTraversal(...$arguments);
//        }
//
//        if ($driver instanceof MySQLDriver) {
//            /** @psalm-var array{version: string} $row */
//            $row = $database->query('SELECT VERSION() AS version')->fetch();
//            $version = $row['version'];
//
//            return str_starts_with($version, '5')
//                ? new MysqlItemTreeTraversal(...$arguments)
//                : new MysqlCteItemTreeTraversal(...$arguments);
//        }
//
//        if ($driver instanceof PostgresDriver) {
//            return new PostgresCteItemTreeTraversal(...$arguments);
//        }
//
//        if ($driver instanceof SQLServerDriver) {
//            return new SqlserverCteItemTreeTraversal(...$arguments);
//        }

        // Ignored due to a complexity of testing and preventing splitting of database argument.
        // @codeCoverageIgnoreStart
        throw new RuntimeException("{$driver->getType()} database driver is not supported.");
        // @codeCoverageIgnoreEnd
    }
}
