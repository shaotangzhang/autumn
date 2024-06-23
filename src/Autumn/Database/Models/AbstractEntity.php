<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Database\Models;

use Autumn\Attributes\Transient;
use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\System\Model;

abstract class AbstractEntity extends Model implements EntityInterface
{
    public const ENTITY_NAME = null;
    public const ENTITY_PRIMARY_COLUMN = null;

    /**
     * @var array<string, array<string, Column>>
     */
    #[Transient]
    private static array $columns = [];

    #[Transient]
    private static array $columnGetters = [];

    #[Transient]
    private static array $columnValidators = [];

    #[Transient]
    private array $hasOneReferences = [];

    #[Transient]
    private array $hasManyReferences = [];

    #[Transient]
    private array $belongsToOneReferences = [];

    #[Transient]
    private array $belongsToManyReferences = [];

    /**
     * Initialize a repository.
     * @param array $options
     * @return RepositoryInterface
     */
    public static function repository(array $options = []): RepositoryInterface
    {
        return new Repository(static::class, $options);
    }

    /**
     * Placeholder for an empty repository.
     *
     * @return RepositoryInterface An empty repository instance.
     */
    public static function none(): RepositoryInterface
    {
        return static::repository()->where('false');
    }

    public static function entity_name(): string
    {
        return static::ENTITY_NAME;
    }

    public static function entity_primary_key(): string
    {
        return static::ENTITY_PRIMARY_COLUMN;
    }

    public static function entity_columns(): array
    {
        if (isset(self::$columns[static::class])) {
            return self::$columns[static::class];
        }

        self::$columns[static::class] = [];
        if ($parentClass = get_parent_class(static::class)) {
            if (is_subclass_of($parentClass, self::class)) {
                self::$columns[static::class] = $parentClass::entity_columns();
            }
        }

        foreach (static::__properties__() as $property) {
            if (!$property->isStatic()) {
                foreach (Column::ofReflection($property) as $column) {
                    $column->setProperty($property);
                    self::$columns[static::class][$column->getName()] = $column;
                }
            }
        }

        return self::$columns[static::class];
    }

    public static function entity_column(string $name): ?Column
    {
        if (isset(self::$columns[static::class][$name])) {
            return self::$columns[static::class][$name];
        }

        foreach (self::entity_columns() as $column) {
            if (!strcasecmp($column->getName(), $name)
                || !strcasecmp($column->getProperty()->getName(), $name)) {
                return $column;
            }
        }

        return null;
    }

    public function toArray(): array
    {
        if (self::$columnGetters[static::class] ??= []) {
            foreach (static::entity_columns() as $name => $column) {
                if ($getter = static::__getter__($column->getProperty()->getName())) {
                    self::$columnGetters[static::class][$name] = $getter;
                } else {
                    self::$columnGetters[static::class][$name] = (fn() => $column->getProperty()->getValue(...))->bindTo($this);
                    exit(__LINE__ . '@' . __CLASS__);
                }
            }
        }

        $data = [];

        foreach (self::$columnGetters[static::class] as $name => $getter) {
            $data[$name] = $this->$getter();
        }

        return $data;
    }

    /**
     * Defines a one-to-one relationship.
     *
     * @param string $relation The related model class name.
     * @param string $localKey The local key used for the relationship.
     * @param string|null $foreignKey The foreign key used for the relationship.
     * @param array|null $context Additional context for the relationship.
     * @return mixed The related model instance or null if not found.
     */
    protected function hasOne(string $relation, string $localKey, string $foreignKey = null, array $context = null): mixed
    {
        return null;
    }

    /**
     * Defines a one-to-many relationship.
     *
     * @param string $relation The related model class name.
     * @param string $localKey The local key used for the relationship.
     * @param string|null $foreignKey The foreign key used for the relationship.
     * @param array|null $context Additional context for the relationship.
     * @return RepositoryInterface The related models' collection.
     */
    protected function hasMany(string $relation, string $localKey, string $foreignKey = null, array $context = null): RepositoryInterface
    {
        return static::none();
    }

    /**
     * Defines a many-to-one relationship.
     *
     * @param string $relation The related model class name.
     * @param string $foreignKey The foreign key used for the relationship.
     * @param string|null $localKey The local key used for the relationship.
     * @param array|null $context Additional context for the relationship.
     * @return mixed The related model instance or null if not found.
     */
    protected function belongsTo(string $relation, string $foreignKey, string $localKey = null, array $context = null): mixed
    {
        return null;
    }

    /**
     * Defines a many-to-many relationship.
     *
     * @param string $relation The related model class name.
     * @param string $foreignKey The foreign key used for the relationship.
     * @param string|null $localKey The local key used for the relationship.
     * @param array|null $context Additional context for the relationship.
     * @return RepositoryInterface The related models' collection.
     */
    protected function belongsToMany(string $relation, string $foreignKey, string $localKey = null, array $context = null): RepositoryInterface
    {
        return static::none();
    }
}