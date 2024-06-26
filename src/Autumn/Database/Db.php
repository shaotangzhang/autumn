<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database;

use Autumn\Database\Interfaces\Creatable;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\Recyclable;
use Autumn\Database\Interfaces\Updatable;
use Autumn\Database\Models\AbstractEntity;
use Autumn\Exceptions\SystemException;
use Autumn\Exceptions\ValidationException;

class Db
{
    public const DEFAULT_DRIVER = 'pdo';
    public const PARAMETER_PREFIX = 'P_';
    public const LIMIT_MAX = 5000;
    public const TIMESTAMP_PARAM_FORMAT = 'Y-m-d H:i:s';

    public const FETCH_META = 1;
    public const FETCH_ASSOC = 2;
    public const FETCH_NUM = 3;
    public const FETCH_BOTH = 4;
    public const FETCH_DATA = 9;

    /**
     * @var array<string, DbConnection>
     */
    private static array $pool = [];

    /**
     * @var array
     */
    private static array $histories = [];

    /**
     * @var DbTransaction[]
     */
    private static array $transactions = [];

    public static function of(string $connectionName = null): ?DbConnection
    {
        if (!$connectionName || ($connectionName === 'default')) {
            return DbConnection::context();
        }

        return self::$pool[$connectionName] ?? new DbConnection($connectionName);
    }

    public static function formatDateTime(\DateTimeInterface $time): string
    {
        return date(static::TIMESTAMP_PARAM_FORMAT, $time->getTimestamp());
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

    public static function forEntity(string|EntityInterface $entity): ?DbConnection
    {
        if ($connection = static::entity_connection_name($entity)) {
            if ($db = Db::of($connection)) {
                return $db;
            }
        }

        return null;
    }


    public static function getConnectionOf(string|DbConnection $connection = null): DbConnection
    {
        if (is_string($connection)) {
            $db = static::of($connection);
            if (!$db) {
                throw SystemException::of('No database connection named %s is configured.', $connection);
            }
        } elseif (!$connection) {
            $db = static::of();
            if (!$db) {
                throw SystemException::of('No default database connection is configured.');
            }
        } else {
            $db = $connection;
        }
        return $db;
    }

    /** for Database ORM << **/

    public static function entity_connection_name(string|EntityInterface $entity): ?string
    {
        // implement complete logic here later.
        return 'default';
    }

    /**
     * Retrieves the entity name by calling the entity's `entity_name` method.
     *
     * @param string|EntityInterface $entity The entity class name or instance.
     * @return string The name of the entity.
     * @throws ValidationException If the entity does not have an `entity_name` method.
     */
    public static function entity_name(string|EntityInterface $entity): string
    {
        if (is_string($entity) && !is_subclass_of($entity, AbstractEntity::class)) {
            throw ValidationException::of("Invalid entity class provided.");
        }

        return call_user_func([$entity, 'entity_name']);
    }

    /**
     * Retrieves the short name of the entity class, transforming it into snake_case.
     *
     * @param string|EntityInterface $entity The entity class name or instance.
     * @return string The short name of the entity in snake_case.
     */
    public static function entity_short_name(string|EntityInterface $entity): string
    {
        static $shortNames;

        $entityClass = is_string($entity) ? $entity : $entity::class;
        if (isset($shortNames[$entityClass])) {
            return $shortNames[$entityClass];
        }

        $pos = strrpos($entityClass, '\\');
        if ($pos !== false) {
            $shortName = substr($entityClass, $pos + 1);
        } else {
            $shortName = $entityClass;
        }

        $lowerName = preg_replace('/(?<!^)([A-Z]+)/', '_\\1', $shortName);
        return $shortNames[$entityClass] = strtolower($lowerName);
    }

    /**
     * Retrieves the primary key column name of the entity by calling the entity's `column_primary_key` method.
     *
     * @param string|AbstractEntity $entity The entity class name or instance.
     * @return string The primary key column name.
     * @throws ValidationException If the entity class is not a subclass of `AbstractEntity`.
     */
    public static function entity_primary_key(string|AbstractEntity $entity): string
    {
        if (!is_subclass_of($entity, AbstractEntity::class)) {
            throw ValidationException::of("Invalid entity class provided.");
        }

        return call_user_func([$entity, 'column_primary_key']);
    }

    /**
     * Generates the local key name for the given entity by transforming its short name into snake_case and appending `_id`.
     *
     * @param string|AbstractEntity $entity The entity class name or instance.
     * @return string The local key name.
     */
    public static function entity_local_key(string|AbstractEntity $entity): string
    {
        return static::entity_short_name($entity) . '_id';
    }

    /**
     * Generates the foreign key name for the given entity by transforming its short name into snake_case and appending `_id`.
     *
     * @param string|AbstractEntity $entity The entity class name or instance.
     * @return string The foreign key name.
     */
    public static function entity_foreign_key(string|AbstractEntity $entity): string
    {
        return static::entity_short_name($entity) . '_id';
    }

    public static function entity_deleted_at(string|Recyclable $entity): string
    {
        return (!is_string($entity) || is_subclass_of($entity, Recyclable::class))
            ? call_user_func([$entity, 'column_deleted_at'])
            : '';
    }

    public static function entity_created_at(string|Creatable $entity): string
    {
        return (!is_string($entity) || is_subclass_of($entity, Creatable::class))
            ? call_user_func([$entity, 'column_created_at'])
            : '';
    }

    public static function entity_updated_at(string|Updatable $entity): string
    {
        return (!is_string($entity) || is_subclass_of($entity, Updatable::class))
            ? call_user_func([$entity, 'column_updated_at'])
            : '';
    }
}