<?php
/**
 * Autumn PHP Framework
 *
 * Date:        9/01/2024
 */

namespace Autumn\Database\Attributes;

use Attribute;
use Autumn\Attributes\AttributeCommonTrait;
use Autumn\Attributes\PropertyAttributeTrait;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    use PropertyAttributeTrait;

    public const AUTO_INCREMENT = true;
    public const AUTO_TIMESTAMP_ON_CREATE = 'create';
    public const AUTO_TIMESTAMP_ON_UPDATE = 'update';

    public const ID = 'id';

    public const FK = 'fk';

    public const PRIORITY_PK = -3;
    public const PRIORITY_FK = -2;
    public const PRIORITY_FIRST = -1;
    public const PRIORITY_DEFAULT = 0;
    public const PRIORITY_TIMESTAMPS = 9999;
    public const PRIORITY_SOFT_DELETION = self::PRIORITY_TIMESTAMPS + 1;
    public const PRIORITY_CHAR = 1000;
    public const PRIORITY_STRING = 1001;
    public const PRIORITY_TEXT = self::PRIORITY_STRING + 10;
    public const PRIORITY_LAST = PHP_INT_MAX;

    public const CHARSET_ASCII = 'ascii';
    public const COLLATION_ASCII = 'ascii_general_ci';
    public const CHARSET_LATIN1 = 'latin1';
    public const COLLATION_LATIN1 = 'latin1_swedish_ci';
    public const CHARSET_UTF8 = 'utf8';
    public const COLLATION_UTF8 = 'utf8_general_ci';
    public const CHARSET_UTF8MB4 = 'utf8mb4';
    public const COLLATION_UTF8MB4 = 'utf8mb4_general_ci';
    public const CHARSET_UNICODE = 'utf8mb4';
    public const COLLATION_UNICODE = 'utf8mb4_unicode_ci';

    public const TYPE_BIT = 'bit';
    public const TYPE_BOOL = 'boolean';
    public const TYPE_CHAR = 'char';
    public const TYPE_TINY_INT = 'tinyint';
    public const TYPE_SMALL_INT = 'smallint';
    public const TYPE_INT = 'int';
    public const TYPE_BIG_INT = 'bigint';
    public const TYPE_FLOAT = 'float';
    public const TYPE_DECIMAL = 'decimal';
    public const TYPE_DOUBLE = 'double';
    public const TYPE_REAL = 'real';
    public const TYPE_STRING = 'varchar';
    public const TYPE_TEXT = 'text';
    public const TYPE_LONG_TEXT = 'longtext';
    public const TYPE_DATE = 'date';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_TIME = 'time';
    public const TYPE_TIMESTAMP = 'timestamp';
    public const TYPE_YEAR = 'year';
    public const TYPE_BINARY = 'binary';
    public const TYPE_VARBINARY = 'varbinary';
    public const TYPE_BLOB = 'blob';
    public const TYPE_ENUM = 'enum';
    public const TYPE_SET = 'set';
    public const TYPE_JSON = 'json';
    public const TYPE_GEOMETRY = 'geometry';
    public const TYPE_POINT = 'point';
    public const TYPE_LINESTRING = 'linestring';
    public const TYPE_POLYGON = 'polygon';
    public const TYPE_MULTIPOINT = 'multipoint';
    public const TYPE_MULTILINESTRING = 'multilinestring';
    public const TYPE_MULTIPOLYGON = 'multipolygon';
    public const TYPE_GEOMETRY_COLLECTION = 'geometrycollection';

    private string $table = '';

    public function __construct(
        private string      $type = '',
        private string      $name = '',
        private int         $size = 0,
        private int         $precision = 0,
        private bool        $unsigned = false,
        private ?bool       $nullable = null,
        private mixed       $default = null,
        private string      $charset = '',
        private string      $collate = '',
        private string      $comment = '',
        private bool|string $auto = false,
        private bool        $currentTimestampOnCreate = false,
        private bool        $currentTimestampOnUpdate = false,
        private bool        $readonly = false,
        private int         $priority = self::PRIORITY_DEFAULT
    )
    {
    }

    public static function from(array $data): static
    {
        $instance = new static(
            $data['type'] ?? '',
            $data['name'] ?? $data['field'] ?? '',
            max($data['size'] ?? 0, 0),
            max($data['precision'] ?? 0, 0),
            $data['unsigned'] ?? false,
            $data['nullable'] ?? $data['null'] ?? null,
            $data['default'] ?? null,
            ($data['charset'] ?? null) ?: '',
            ($data['collate'] ?? $data['collation'] ?? null) ?: '',
            ($data['comment'] ?? null) ?: '',
            (bool)($data['auto_increment'] ?? $data['auto'] ?? false),
            $data['currentTimestampOnCreate'] ?? false,
            $data['currentTimestampOnUpdate'] ?? false,
        );

        if ($table = $data['table'] ?? null) {
            $instance->table = $table;
        }
        return $instance;
    }

    public static function formatColumnName(string $column): string
    {
        return preg_replace_callback(
            '/[A-Z]/',
            fn(array $m) => '_' . strtolower($m[0]),
            $column
        );
    }

    public static function formatColumn(string $column, string $alias = null): string
    {
        if (!empty($alias)) {
            $alias = " AS `$alias`";
        }

        if (preg_match('/[(\s)]+/', $column)) {
            return $column . $alias;
        }

        return static::formatColumnName($column) . $alias;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getValue(object $object): mixed
    {
        if ($this->property) {
            return $this->property->getValue($object);
        }

        if ($name = $this->getName()) {
            return $object->$name ?? null;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    public function getField(): string
    {
        return $this->name;
    }

    /**
     * @return bool|string
     */
    public function getAuto(): bool|string
    {
        return $this->auto;
    }

    public function isAuto(): bool
    {
        return $this->auto === true;
    }

    public function hasDefaultValue(): bool
    {
        return $this->getDefault() !== null;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default ?? $this->getPropertyDefault();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?: ($this->name = static::formatColumnName($this->getPropertyName()));
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @return string
     */
    public function getCollate(): string
    {
        return $this->collate;
    }

    /**
     * @return bool
     */
    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable !== false;
    }

    /**
     * @return bool
     */
    public function isNotNull(): bool
    {
        return $this->nullable === false;
    }

    /**
     * @return bool|null
     */
    public function getNullable(): ?bool
    {
        return $this->nullable;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @return bool
     */
    public function isCurrentTimestampOnCreate(): bool
    {
        return $this->currentTimestampOnCreate;
    }

    /**
     * @return bool
     */
    public function isCurrentTimestampOnUpdate(): bool
    {
        return $this->currentTimestampOnUpdate;
    }

    /**
     * @return bool
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    public function isString(): bool
    {
        return str_contains($this->type, 'char')
            || str_contains($this->type, 'text')
            || str_contains($this->type, 'binary')
            || str_contains($this->type, 'bit')
            || str_contains($this->type, 'set')
            || str_contains($this->type, 'enum');
    }

    public function isInt(): bool
    {
        return str_contains($this->type, 'int');
    }

    public function isFloat(): bool
    {
        return str_contains($this->type, 'real')
            || str_contains($this->type, 'float')
            || str_contains($this->type, 'double')
            || str_contains($this->type, 'decimal');
    }

    public function isBool(): bool
    {
        return str_contains($this->type, 'bool');
    }

    public function getDataType(): string
    {
        return match ($type = $this->getType()) {
            static::ID, static::FK => 'bigint',
            static::TYPE_JSON => 'json',
            default => $type,
        };
    }

    protected function getPropertyDefault(): mixed
    {
        if ($this->property && $this->property->hasDefaultValue()) {
            return $this->property->getDefaultValue();
        }
        return null;
    }

    protected function getPropertyName(): string
    {
        return $this->property?->getName() ?? '';
    }

    public function isPrimaryKey(): bool
    {
        return $this->type === static::ID;
    }

    public function isForeignKey(): bool
    {
        return $this->type === static::FK;
    }

//    public function isIgnorable(mixed $value): bool
//    {
//        return ($ignores = $this->getIgnore())
//            && \Autumn\Lang\JSON::isIgnoble($ignores, $value);
//    }
//
//    public function getIgnore(): int
//    {
//        foreach ($this->property?->getAttributes(Json::class) ?? [] as $attribute) {
//            $json = $attribute->newInstance();
//            return $json->getIgnore();
//        }
//
//        return false;
//    }

    public function getValueOrIgnore(object $instance): mixed
    {
        if ($this->property) {
            foreach ($this->property->getAttributes(JsonIgnore::class) as $attribute) {
                $ignore = $attribute->newInstance();
                $value = $ignore->getValueOfIgnore($this->property, $instance);
                if ($value !== null) {
                    return $value;
                }
            }

            return $this->property->getValue($instance);
        }

        return null;
    }
}