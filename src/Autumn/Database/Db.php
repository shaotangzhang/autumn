<?php
namespace Autumn\Database;

use Autumn\Database\Models\AbstractEntity;

class Db
{
    public const DEFAULT_DRIVER = 'pdo';
    public const PARAMETER_PREFIX = 'P_';
    public const TIMESTAMP_PARAM_FORMAT = 'Y-m-d H:i:s';

    public const FETCH_META = 1;
    public const FETCH_ASSOC = 2;
    public const FETCH_NUM = 3;
    public const FETCH_BOTH = 4;
    public const FETCH_DATA = 9;

    private static array $pool = [];

    private static array $histories = [];

    public static function of(string $connectionName = null): ?DbConnection
    {
        if (!$connectionName || ($connectionName === 'default')) {
            return DbConnection::context();
        }

        return self::$pool[$connectionName] ?? new DbConnection($connectionName);
    }

    public static function forEntity(string|object $entity): ?DbConnection
    {
        if ($connection = static::connection($entity)) {
            if ($db = Db::of($connection)) {
                if (($entity instanceof AbstractEntity) && method_exists($entity, '__connect__')) {
                    $entity->__connect__($db);
                }
                return $db;
            }
        }

        return null;
    }

    public static function connection(string|object $entity): ?string
    {
        return 'default';
    }

    public static function histories(): array
    {
        return self::$histories;
    }

    public static function addHistory(string $sql, array $params = []): void
    {
        self::$histories[] = [
            'sql' => $sql,
            'params' => $params,
        ];
    }

    public static function formatDateTime(\DateTimeInterface $time): string
    {
        return date(static::TIMESTAMP_PARAM_FORMAT, $time->getTimestamp());
    }

    public static function query(string $sql, array $parameters = null): DbResultset
    {
        return static::of()->query($sql, $parameters);
    }

    /**
     * @throws DbException
     */
    public static function execute(string $sql, array $parameters = null): ?int
    {
        return static::of()->execute($sql, $parameters);
    }
}