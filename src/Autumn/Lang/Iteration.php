<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/01/2024
 */

namespace Autumn\Lang;

use Autumn\Interfaces\ArrayInterface;

/**
 * The Iteration class provides utility methods for iterable and array operations.
 *
 * @package Api\Controllers
 */
class Iteration
{

    /**
     * Apply a callback function to the elements of multiple iterables and return the result as an array.
     *
     * @param callable $callback The callback function to apply to each element.
     * @param iterable ...$data Variable number of iterables.
     * @return array The resulting array.
     */
    public static function map(callable $callback, iterable ...$data): array
    {
        $list = [];
        foreach ($data as $array) {
            foreach ($array as $index => $value) {
                if (is_int($index)) {
                    $list[] = call_user_func($callback, $value, $index, $array);
                } else {
                    $list[$index] = call_user_func($callback, $value, $index, $array);
                }
            }
        }

        return $list;
    }

    /**
     * Map the given data using the provided callback function.
     *
     * @param mixed $data The data to be mapped.
     * @param callable $callback The callback function to apply for each element.
     * @param mixed ...$args Additional arguments to pass to the callback.
     *
     * @return array
     */
    public static function mapping(mixed $data, callable $callback, mixed ...$args): array
    {
        $result = [];
        static::iterate($data, fn(mixed $value, int|string $key) => $result[$key] = call_user_func($callback, $value, $key, ...$args));
        return $result;
    }

    /**
     * Iterate over the given data, invoking the callback for each element.
     *
     * @param mixed $data The data to iterate over.
     * @param callable|null $callback The callback function to invoke for each element.
     * @param mixed ...$args Additional arguments to pass to the callback.
     *
     * @return \Traversable
     */
    public static function iterate(mixed $data, callable $callback = null, mixed ...$args): \Traversable
    {
        if ($callback) {
            if (is_iterable($data)) {
                foreach ($data as $name => $value) {
                    yield $name => call_user_func($callback, $value, $name, ...$args);
                }
            } else {
                yield $data;
            }
        } elseif (is_iterable($data)) {
            yield from $data;
        } elseif ($data !== null) {
            yield $data;
        }
    }

    /**
     * Generate key-value pairs from the provided data using a callback function.
     *
     * If a callback is provided, it is invoked for each element in the data. The callback
     * receives the element value and key, and its return value determines the yielded result.
     * If the callback returns an array with two elements [value, key], it yields that array.
     * Otherwise, it assumes the callback returns a value, and it yields an array with that value
     * and the original key.
     *
     * If no callback is provided and the data is iterable, it yields key-value pairs for each
     * element in the iterable. If the data is not iterable but not null, it yields a single
     * key-value pair with the data and a default key of 0.
     *
     * @param mixed $data The data to iterate over.
     * @param callable|null $callback The callback function to apply to each element.
     * @param mixed ...$args Additional arguments to pass to the callback.
     *
     * @return \Traversable The generator yielding key-value pairs based on the provided data.
     *
     * @note If a callback is used, be aware of its return format: [value, key] for direct yield,
     *       or just value with the original key.
     */
    public static function entries(mixed $data, callable $callback = null, mixed ...$args): \Traversable
    {
        if ($callback) {
            foreach (static::iterate($data) as $name => $item) {
                $result = call_user_func($callback, $item, $name, ...$args);
                if (is_array($result) && isset($result[1]) && (count($result) === 2)) {
                    yield $result;
                } else {
                    yield [$result, $name];
                }
            }
        } elseif (is_iterable($data)) {
            foreach ($data as $name => $value) {
                yield [$value, $name];
            }
        } elseif ($data !== null) {
            yield [$data, 0];
        }
    }

    /**
     * Reproduces entries from the input data, applying a callback if provided, and yields the results.
     *
     * @param mixed $data The input data (iterable or non-null).
     * @param callable|null $callback Optional callback to process each entry.
     * @param mixed ...$args Additional arguments passed to the callback.
     *
     * @return \Traversable      A generator yielding reproduced entries.
     */
    public static function replicate(mixed $data, callable $callback = null, mixed ...$args): \Traversable
    {
        if ($callback) {
            if (is_iterable($data)) {
                foreach ($data as $name => $value) {
                    $result = call_user_func($callback, $value, $name, ...$args);
                    if (is_array($result) && isset($result[1]) && (count($result) === 2)) {
                        yield ($result[1] ?? $name) => ($result[0] ?? null);
                    } else {
                        yield $name => $result;
                    }
                }
            } else {
                yield $data;
            }
        } elseif (is_iterable($data)) {
            yield from $data;
        } elseif ($data !== null) {
            yield $data;
        }
    }


    public static function toArray(mixed $data, bool $inJSON = null): array
    {
        if ($data === null) {
            return [];
        }

        if (is_array($data)) {
            return $data;
        }

        if ($inJSON && ($data instanceof \JsonSerializable)) {
            $data = $data->jsonSerialize();
        }

        if ($data instanceof ArrayInterface) {
            return $data->toArray();
        }

        if ($data instanceof \Traversable) {
            return iterator_to_array($data);
        }

        return (array)$data;
    }


    /**
     * Convert the input data to an array.
     *
     * @param mixed $data The data to be converted.
     * @return array The resulting array.
     */
    public static function toArrayRecursive(mixed $data, bool $inJSON = null): array
    {
        return (array)static::toArrayRecursiveCallback($data, $inJSON);
    }

    public static function toArrayRecursiveCallback(mixed $data, bool $inJSON = null, callable $callback = null, mixed ...$args): mixed
    {
        if ($data === null) {
            return null;
        }

        if ($inJSON && ($data instanceof \JsonSerializable)) {
            $data = $data->jsonSerialize();
        }

        if ($data instanceof ArrayInterface) {
            $data = $data->toArray();
        } elseif ($data instanceof \Traversable) {
            $data = iterator_to_array($data);
        }

        if (isset($callback) && is_array($data)) {
            array_walk($data, fn($v) => static::toArrayRecursiveCallback($v, $inJSON, $callback, ...$args));
        }

        return $data;
    }

    /**
     * Extract values from an iterable using a specified key and return them as an array.
     *
     * @param iterable $data The iterable to extract values from.
     * @param int|string $key The key to extract values by.
     * @return array The resulting array of values.
     */
    public static function column(iterable $data, int|string $key): array
    {
        $list = [];
        foreach ($data as $item) {
            $list[] = $item[$key];
        }
        return $list;
    }

    public static function toPair(mixed $data, int|string $keyColumn = null, int|string|callable|null $mapping = null, int|string ...$others): iterable
    {
        if (!is_callable($mapping)) {
            if ($mapping !== null) {
                array_unshift($others, $mapping);
                $mapping = null;
            }
        }

        foreach (static::iterate($data) as $key => $item) {
            if ($mapping === null) {
                $data = null;
                if (empty($others)) {
                    $data = $item;
                } else {
                    foreach ($others as $other) {
                        if ($other !== null) {
                            if (is_array($other)) {
                                $data = array_merge($data ?? [], $other);
                            } else {
                                $data[$other] = $item[$other] ?? null;
                            }
                        }
                    }
                }
            } else {
                $data = call_user_func($mapping, $item, $key);
            }

            if ($keyColumn === null) {
                yield $key => $data;
            } elseif (($name = $item[$keyColumn] ?? null) !== null) {
                yield (string)$name => $data;
            } else {
                yield $data;
            }
        };
    }

    public static function flatten(mixed $data, string $prefix = null, string $separator = '.'): mixed
    {
        if ($data === null) {
            return null;
        }

        if ($data instanceof \JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        if ($data instanceof ArrayInterface) {
            $data = $data->toArray();
        }

        if (is_iterable($data)) {
            $list = [];
            foreach ($data as $key => $value) {
                if (is_int($key)) {
                    $list[$key] = static::flatten($value, "$prefix\[$key\]", $separator);
                } else {
                    $list[$key] = static::flatten($value, $prefix ? $prefix . $separator . $key : $key);
                }
            }

            return $list;
        }

        return $data;
    }

    public static function first(mixed $data): mixed
    {
        foreach (static::toArray($data) as $item) {
            return $item;
        }

        return null;
    }

    /**
     * @param mixed $data
     * @param callable|null $callback
     * @param mixed ...$args
     * @return \Traversable
     */
    public static function iterateAllRecursive(mixed $data, callable $callback = null, mixed ...$args): \Traversable
    {
        if ($data === null) {
            return [];
        }

        if (!is_iterable($data)) {
            if ($callback) {
                yield call_user_func($callback, $data, 0, ...$args);
            } else {
                yield $data;
            }
        }

        foreach ($data as $key => $value) {
            if (is_iterable($value)) {
                yield from static::iterateAllRecursive($value, $callback, $key, ...$args);
            } elseif ($callback) {
                yield call_user_func($callback, $value, $key, ...$args);
            } else {
                yield $value;
            }
        }
    }

    /**
     * @param iterable $data
     * @param callable|null $callback
     * @param mixed ...$args
     * @return \Traversable
     */
    public static function iterateRecursive(iterable $data, callable $callback = null, mixed ...$args): \Traversable
    {
        foreach ($data as $key => $value) {
            if (is_iterable($value)) {
                yield from static::iterateRecursive($value, $callback, $key, ...$args);
            } elseif ($callback) {
                yield call_user_func($callback, $value, $key, ...$args);
            } else {
                yield $value;
            }
        }
    }

    /**
     * @param iterable $iterable
     * @param callable $callback
     * @param mixed|null $initial
     * @return mixed
     */
    public static function reduce(iterable $iterable, callable $callback, mixed $initial = null): mixed
    {
        $accumulator = $initial;

        foreach ($iterable as $key => $value) {
            $accumulator = $callback($accumulator, $value, $key);
        }

        return $accumulator;
    }

    public static function reduceAll(mixed $data, callable $callback, mixed $initial = null): mixed
    {
        if ($data === null) {
            return $initial;
        }

        if (!is_iterable($data)) {
            return call_user_func($callback, $initial, $data, 0);
        }

        return static::reduce($data, $callback, $initial);
    }

    public static function process(mixed $data, callable $callback, mixed ...$args): array
    {
        $list = [];

        if (is_iterable($data)) {
            foreach ($data as $name => $value) {
                $list[$name] = $value;
                call_user_func($callback, $value, $name, ...$args);
            }
        } else {
            $list = [$data];
            call_user_func($callback, $data, 0, ...$args);
        }

        return $list;
    }
}