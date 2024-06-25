<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Models;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\Persistable;
use Autumn\System\Model;

/**
 * Abstract entity class providing common functionality for entities.
 *
 * This class is intended to be extended by specific entity classes in a custom framework.
 * It manages column metadata, guarded and fillable properties, and provides methods for
 * accessing and manipulating entity data.
 */
abstract class AbstractEntity extends Model implements EntityInterface, Persistable
{
    public const ENTITY_NAME = null;
    public const GUARDED_COLUMNS = null;
    public const FILLABLE_COLUMNS = null;

    /**
     * @var array<string, array<string, Column>> Stores columns metadata for each entity class.
     */
    private static array $columns = [];

    /**
     * @var array<string, array<string, \ReflectionProperty>> Stores reflection properties for each entity class.
     */
    private static array $properties = [];

    /**
     * @var array<string, array<string, Column>> Stores guarded columns for each entity class.
     */
    private static array $guarded = [];

    /**
     * @var array<string, array<string, Column>> Stores fillable columns for each entity class.
     */
    private static array $fillables = [];

    /**
     * Returns the entity/datatable name.
     *
     * @return string The name of the entity/datatable.
     */
    public static function entity_name(): string
    {
        return static::ENTITY_NAME ?? '';
    }

    /**
     * Retrieves the names of guarded properties from constant or string configuration.
     *
     * @return array<string> The names of guarded properties.
     */
    public static function entity_guarded_property_names(): array
    {
        $guarded = static::GUARDED_COLUMNS;
        if (is_array($guarded)) {
            return $guarded;
        }

        if (!is_string($guarded)) {
            return [];
        }

        return preg_split('/\s*[,;|]+\s*/', trim($guarded, " \n\r\t\v\0,;|"));
    }

    /**
     * Retrieves the names of fillable properties from constant or string configuration.
     *
     * @return array<string> The names of fillable properties.
     */
    public static function entity_fillable_property_names(): array
    {
        $fillables = static::FILLABLE_COLUMNS;
        if (is_array($fillables)) {
            return $fillables;
        }

        if (!is_string($fillables)) {
            return [];
        }

        return preg_split('/\s*[,;|]+\s*/', trim($fillables, " \n\r\t\v\0,;|"));
    }

    /**
     * Retrieves the columns metadata for the entity class.
     *
     * @return array<string, Column> The columns metadata.
     */
    public static function entity_columns(): array
    {
        if (!isset(self::$columns[static::class])) {
            self::$columns[static::class] = [];

            // If parent class exists and implements EntityInterface, inherit columns
            if (($parentClass = get_parent_class(static::class)) && is_subclass_of($parentClass, EntityInterface::class)) {
                self::$columns[static::class] = $parentClass::entity_columns();
            }

            $guarded = static::entity_guarded_property_names();
            $fillables = static::entity_fillable_property_names();

            $reflection = new \ReflectionClass(static::class);

            foreach ($reflection->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
                foreach (Column::forReflection($property) as $column) {
                    self::$columns[static::class][$property->name] = $column;
                    self::$properties[static::class][$column->getName()] = $property;

                    if (in_array($property->name, $guarded)) {
                        self::$guarded[static::class][$property->name] = $column;
                        // Once guarded, no need to check further
                        break;
                    }

                    if (in_array($column->getType(), [Column::ID, Column::FK])) {
                        self::$guarded[static::class][$property->name] = $column;
                        // Once guarded, no need to check further
                        break;
                    }

                    if ($column->isCurrentTimestampOnCreate() || $column->isCurrentTimestampOnUpdate()) {
                        self::$guarded[static::class][$property->name] = $column;
                    } elseif (is_null($fillables) || in_array($property->name, $fillables)) {
                        self::$fillables[static::class][$property->name] = $column;
                    }
                }
            }
        }

        return self::$columns[static::class];
    }

    /**
     * Retrieves the reflection properties for the entity class.
     *
     * @return array<string, \ReflectionProperty> The reflection properties.
     */
    public static function entity_properties(): array
    {
        if (!isset(self::$properties[static::class])) {
            self::$properties[static::class] = [];
            static::entity_columns(); // Ensure columns are loaded
        }

        return self::$properties[static::class];
    }

    /**
     * Retrieves the guarded columns for the entity class.
     *
     * @return Column[] The guarded columns.
     */
    public static function entity_guarded_columns(): array
    {
        if (!isset(self::$guarded[static::class])) {
            self::$guarded[static::class] = [];
            static::entity_columns(); // Ensure columns are loaded
        }

        return self::$guarded[static::class];
    }

    /**
     * Retrieves the fillable columns for the entity class.
     *
     * @return Column[] The fillable columns.
     */
    public static function entity_fillable_columns(): array
    {
        if (!isset(self::$fillables[static::class])) {
            self::$fillables[static::class] = [];
            static::entity_columns(); // Ensure columns are loaded
        }

        return self::$fillables[static::class];
    }

    /**
     * Checks if a property is guarded based on its name.
     *
     * @param string $propertyName The name of the property.
     * @return bool Whether the property is guarded.
     */
    public static function property_is_guarded(string $propertyName): bool
    {
        return isset(static::entity_guarded_columns()[$propertyName]);
    }

    /**
     * Checks if a column is guarded based on its name.
     *
     * @param string $columnName The name of the column.
     * @return bool Whether the column is guarded.
     */
    public static function column_is_guarded(string $columnName): bool
    {
        if ($property = static::entity_column_to_property($columnName)) {
            return static::property_is_guarded($property->name);
        }

        return false;
    }

    /**
     * Checks if a property is fillable based on its name.
     *
     * @param string $propertyName The name of the property.
     * @return bool Whether the property is fillable.
     */
    public static function property_is_fillable(string $propertyName): bool
    {
        return isset(static::entity_fillable_columns()[$propertyName]);
    }

    /**
     * Checks if a column is fillable based on its name.
     *
     * @param string $columnName The name of the column.
     * @return bool Whether the column is fillable.
     */
    public static function column_is_fillable(string $columnName): bool
    {
        if ($property = static::entity_column_to_property($columnName)) {
            return static::property_is_fillable($property->name);
        }

        return false;
    }

    /**
     * Retrieves the Column object corresponding to a property name.
     *
     * @param string $propertyName The name of the property.
     * @return Column|null The Column object, or null if not found.
     */
    public static function entity_property_to_column(string $propertyName): ?Column
    {
        return static::entity_columns()[$propertyName] ?? null;
    }

    /**
     * Retrieves the ReflectionProperty object corresponding to a column name.
     *
     * @param string $columnName The name of the column.
     * @return \ReflectionProperty|null The ReflectionProperty object, or null if not found.
     */
    public static function entity_column_to_property(string $columnName): ?\ReflectionProperty
    {
        return static::entity_properties()[$columnName] ?? null;
    }

    /**
     * Retrieves the ReflectionProperty object for a given name.
     *
     * @param string $name The name of the property or column.
     * @return \ReflectionProperty|null The ReflectionProperty object, or null if not found.
     */
    public static function entity_get_property(string $name): ?\ReflectionProperty
    {
        return static::entity_column_to_property($name)
            ?? static::entity_property_to_column($name)?->getProperty();
    }

    /**
     * Retrieves the Column attribute for a given name.
     *
     * @param string $name The name of the property or column.
     * @return Column|null The ReflectionProperty object, or null if not found.
     */
    public static function entity_get_column(string $name): ?Column
    {
        if ($column = static::entity_property_to_column($name)) {
            return $column;
        }

        if ($property = static::entity_column_to_property($name)) {
            return static::entity_property_to_column($property->getName());
        }

        return null;
    }

    /**
     * Retrieves the value of a property.
     *
     * @param string $property The name of the property.
     * @param mixed $default The default value to return if property not found.
     * @return mixed The value of the property or the default value.
     */
    protected function __property_get__(string $property, mixed $default = null): mixed
    {
        if ($property = static::entity_get_property($property)) {
            return $property->getValue($this);
        }

        return $default;
    }

    /**
     * Checks if a property is set.
     *
     * @param string $property The name of the property.
     * @return bool Whether the property is set.
     */
    protected function __property_isset__(string $property): bool
    {
        if ($property = static::entity_get_property($property)) {
            return $property->getValue($this) !== null;
        }

        return false;
    }

    /**
     * Resets a property to its default value.
     *
     * @param string $property The name of the property.
     * @return bool Whether the property was reset.
     */
    protected function __property_reset__(string $property): bool
    {
        if ($property = static::entity_get_property($property)) {
            if ($property->hasDefaultValue()) {
                $property?->setValue($this, $property->getDefaultValue());
                return true;
            }
        }

        return false;
    }

    /**
     * Sets entity properties from an associative array.
     *
     * @param array $data The data array.
     * @return static The current instance of the entity.
     */
    public function fromArray(array $data): static
    {
        foreach ($data as $name => $value) {
            $this->__set($name, $value);
        }

        return $this;
    }

    /**
     * Converts entity properties to an associative array.
     *
     * @return array The associative array representation of entity properties.
     */
    public function toArray(): array
    {
        $data = [];
        foreach (static::entity_columns() as $column) {
            $data[$column->getName()] = $column->getProperty()->getValue($this);
        }
        return $data;
    }
}
