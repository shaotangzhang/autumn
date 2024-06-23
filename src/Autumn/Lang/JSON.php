<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/12/2023
 */

namespace Autumn\Lang;

use Autumn\Interfaces\ArrayInterface;

class JSON
{
    public const IGNORE_NULL = 1;
    public const IGNORE_ZERO = 2;
    public const IGNORE_BLANK = 4;
    public const IGNORE_EMPTY_ARRAY = 7;
    public const IGNORE_EMPTY = self::IGNORE_NULL | self::IGNORE_ZERO | self::IGNORE_BLANK | self::IGNORE_EMPTY_ARRAY;

    /**
     * @throws \JsonException
     */
    public static function encode(mixed $data): string
    {
        $encoded = json_encode($data);
        if ($encoded === false) {
            if ($error = json_last_error()) {
                throw new \JsonException(json_last_error_msg(), $error);
            }
        }

        return $encoded;
    }

    public static function encodeDateTime(\DateTimeInterface $time): string
    {
        return $time->format('c');
    }

    /**
     * Converts a DateTimeInterface object to a string according to the specified format or default format.
     *
     * @param \DateTimeInterface $dateTime - DateTimeInterface object to convert
     * @param callable|null $callback - Optional callback function to format the DateTime object
     * @return string - Converted DateTime as a string
     */
    public static function dateTimeToString(\DateTimeInterface $dateTime, callable $callback = null): string
    {
        if ($callback === null) {
            return $dateTime->format(env('JSON_DATETIME_FORMAT', 'c'));
        }

        return call_user_func($callback, $dateTime);
    }

    /**
     * Converts an object to JSON string.
     *
     * @param object $object - Object to be converted to JSON
     * @param callable|null $callback - Optional callback function to process the object before JSON conversion
     * @return string - JSON-encoded object
     * @throws \JsonException
     */
    public static function convertObjectToJSON(object $object, callable $callback = null): string
    {
        if ($object instanceof ArrayInterface) {
            return static::convertArrayToJSON($object->toArray(), $callback);
        }

        if ($object instanceof \DateTimeInterface) {
            $object = static::dateTimeToString($object);
        }

        if ($callback !== null) {
            $object = call_user_func($callback, $object);
        }

        return static::encode($object);
    }

    /**
     * Converts an array to JSON string.
     *
     * @param array $data - Array to be converted to JSON
     * @param callable|null $callback - Optional callback function to process the array before JSON conversion
     * @return string - JSON-encoded array
     * @throws \JsonException
     */
    public static function convertArrayToJSON(array $data, callable $callback = null): string
    {
        foreach ($data as $key => $value) {
            $data[$key] = static::stringify($value, $callback);
        }

        return static::encode($data);
    }

    /**
     * Converts data to JSON string based on its type.
     *
     * @param mixed $data - Data to be converted to JSON
     * @param callable|null $callback - Optional callback function to process the data before JSON conversion
     * @return string - JSON-encoded data
     * @throws \JsonException
     */
    public static function stringify(mixed $data, callable $callback = null): string
    {
        static $null, $true, $false;

        switch (gettype($data)) {
            case 'NULL':
                return $null ??= json_encode(null);

            case 'boolean':
                return $data
                    ? ($true ??= json_encode(true))
                    : ($false ??= json_encode(false));

            case 'array':
                return static::convertArrayToJSON($data, $callback);

            case 'object':
                if ($callback !== null) {
                    return static::convertObjectToJSON($data);
                }

                $value = call_user_func($callback, $data);
                return static::stringify($value);

            default:
                return json_encode((string)$data);
        }
    }

    /**
     * @throws \JsonException
     */
    public static function load(string $file, bool $returnObject = null): array|object|null
    {
        $data = file_get_contents($file);
        if ($data === false) {
            return null;
        }

        return static::parse($data, $returnObject);
    }

    public static function ignoreArray(array $data, int|callable $mode): ?array
    {
        if (is_int($mode)) {
            return match ($mode) {
                static::IGNORE_EMPTY => array_filter($data),
                static::IGNORE_BLANK => array_filter($data, fn($v) => !is_string($v) || (trim($v) !== '')),
                static::IGNORE_EMPTY_ARRAY => array_filter($data, fn($v) => $v !== []),
                static::IGNORE_ZERO => array_filter($data, fn($v) => $v !== 0),
                static::IGNORE_NULL => array_filter($data, fn($v) => isset($v)),
                default => $data,
            };
        }

        $data = array_filter($data, $mode);
        if (($data === []) && ($mode | static::IGNORE_EMPTY_ARRAY)) {
            return null;
        }
        return $data;
    }

    public static function ignore(mixed $data, int|callable $mode = null): mixed
    {
        if (!$mode) {
            return $data;
        }

        if ($data instanceof \JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        if ($data instanceof ArrayInterface) {
            $data = $data->toArray();
        } elseif ($data instanceof \Traversable) {
            $data = iterator_to_array($data);
        } elseif (!is_array($data)) {
            return $data;
        }

        return static::ignoreArray($data, $mode);
    }

    public static function ignoreRecursive(mixed $data, int|callable $mode = null): mixed
    {
        $data = static::ignore($data, $mode);

        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = static::ignoreRecursive($value, $mode);
            }
        }

        return $data;
    }

    public static function isIgnoble(int $ignores, mixed $value): bool
    {
        return match ($ignores) {
            -1 => true,
            static::IGNORE_NULL => $value === null,
            static::IGNORE_ZERO => $value === 0,
            static::IGNORE_BLANK => is_string($value) && (trim($value) === ''),
            static::IGNORE_EMPTY_ARRAY => $value === [],
            static::IGNORE_EMPTY => empty($value),
            default => false,
        };
    }

    /**
     * Parse JSON data.
     *
     * @param string $data The JSON string to parse.
     * @param bool $returnObject Whether to return an object instead of an array (default: false).
     * @param bool $silent Whether to suppress errors and return null on failure (default: false).
     *
     * @return mixed The parsed JSON data.
     * @throws \JsonException If the JSON cannot be decoded and $silent is false.
     */
    public static function parse(string $data, bool $returnObject = false, bool $silent = false): mixed
    {
        if ($silent) {
            // Decode JSON silently (without throwing errors)
            return json_decode($data, !$returnObject);
        } else {
            // Decode JSON and throw an exception on error
            return json_decode($data, !$returnObject, 512, JSON_THROW_ON_ERROR);
        }
    }

}
