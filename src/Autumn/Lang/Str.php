<?php
/**
 * Autumn PHP Framework
 *
 * Date:        27/01/2024
 */

namespace Autumn\Lang;

class Str
{
    public static function toUTF8(string $text = null, string $charset = null): string
    {
        if (!$text || ($charset && (strtolower($charset) === 'utf-8'))) {
            return $text ?? '';
        }

        try {
            return mb_convert_encoding($text, 'utf-8', $charset);
        } catch (\Throwable) {
            if ($charset) {
                try {
                    return iconv($charset, 'utf-8//IGNORE', $text);
                } catch (\Throwable) {
                }
            }
        }

        return $text;
    }

    public function serialize(mixed $value): string
    {
        return serialize($value);
    }

    public function unserialize(string $code, array $options = null): mixed
    {
        return unserialize($code, $options ?? []);
    }

    public static function string(mixed $value): ?string
    {
        if (static::stringable($value)) {
            return (string)$value;
        }

        return null;
    }

    public static function stringable(mixed $value): bool
    {
        if (is_string($value)) {
            return true;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return true;
        }

        return false;
    }

    public static function format(string $format, array|object $item, callable $fallback = null, mixed ...$args): string
    {
        static $defaultFallback;
        static $pattern = '/{\s*(\w+)\s*}/';

        $fallback ??= ($defaultFallback ??= fn($key, $item) => null);

        $func = (is_array($item) || ($item instanceof \ArrayAccess))
            ? (fn($key) => $item[$key] ?? $fallback($item, $key, ...$args))
            : (fn($key) => $item->$key ?? $fallback($item, $key, ...$args));

        return preg_replace_callback($pattern, fn($matches) => $func($matches[1]) ?? $matches[0], $format);
    }

    public static function toPascalCase(string $text): string
    {
        $str = preg_replace('/[^a-zA-Z0-9]+/', ' ', $text);
        $str = ucwords(trim($str));
        return str_replace(' ', '', $str);
    }

    public static function toCamelCase(string $text): string
    {
        return lcfirst(static::toPascalCase($text));
    }

    public static function toSnakeCase(string $text): string
    {
        // 使用正则表达式捕获连续的大写字母序列，加上下划线
        $str = preg_replace('/([A-Z]+)/', '_\\1', $text);

        // 将非字母数字字符替换为下划线，并转换为小写
        $str = preg_replace('/[^a-z0-9]+/', '_', $str);

        // 将首尾的下划线去除
        return strtolower(trim($str, '_'));
    }

    public static function toKebabCase(string $text): string
    {
        // 使用正则表达式捕获连续的大写字母序列，加上连字符
        $str = preg_replace('/([A-Z]+)/', '-\\1', $text);

        // 将非字母数字字符替换为连字符，并转换为小写
        $str = preg_replace('/[^a-z0-9]+/', '-', $str);

        // 将首尾的连字符去除
        return strtolower(trim($str, '-'));
    }


}