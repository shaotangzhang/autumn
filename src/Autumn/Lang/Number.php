<?php
/**
 * Autumn PHP Framework
 *
 * Date:        26/12/2023
 */

namespace Autumn\Lang;

class Number
{
    public static function getSystemDecimalPoint(): string
    {
        static $dotPoint;

        if ($dotPoint === null) {
            $dotPoint = env('SYSTEM_DECIMAL_POINT');

            if (!is_string($dotPoint) || strlen($dotPoint) !== 1) {
                $dotPoint = static::getLocaleDecimalPoint() ?? '.';
            }
        }

        return $dotPoint;
    }

    public static function getLocaleDecimalPoint(): string
    {
        if (function_exists('localeconv')) {
            $info = localeconv();
            $dotPoint = $info['decimal_point'] ?? null;

            if (is_string($dotPoint) && strlen($dotPoint) === 1) {
                return $dotPoint;
            }
        }

        return static::getDefaultDecimalPoint();
    }

    public static function getDefaultDecimalPoint(): string
    {
        $dotPoint = substr(number_format(0, 1), 1, 1);
        return !empty($dotPoint) ? $dotPoint : '.';
    }

    public static function toFileBytes(mixed $value, int $decimals = 2, array $sizes = null, bool $noSpaceBetween = null): string
    {
        $bytes = static::int($value ?: 0);
        if ($bytes === null) return '';

        $k = 1024;
        $sizes ??= ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        if ($i = (int)floor(log($bytes) / log($k))) {
            if ($n = pow($k, $i)) {
                $n = number_format(floatval(($bytes / $n)), $decimals ??= 2);
            }
        }else{
            $n = $bytes;
        }

        return $n . ($noSpaceBetween ? '' : ' ') . $sizes[$i];
    }

    public static function parseBytes(string $value): int|float|null
    {
        if (preg_match('/^\s*(\d+(?:,\d\d\d)*(?:\.\d+))\s*([kmgtp])(\s*b(?:ytes)?)?\s*$/i', $value, $matches)) {
            $value = static::numeric(str_replace(',', '', $matches[1]));
            switch (strtolower($matches[2])) {
                case 'p':
                    $value *= 1024.0;
                // no break
                case 't':
                    $value *= 1024.0;
                // no break
                case 'g':
                    $value *= 1024;
                // no break
                case 'm':
                    $value *= 1024;
                // no break
                case 'k':
                    $value *= 1024;
                // no break
            }

            return $value;
        }

        return static::numeric($value);
    }

    public static function numeric(?string $value): int|float|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (str_ends_with($value, '%')) {
            $rate = 100.00;
            $value = substr($value, 0, -1);
        } else {
            $rate = 1.00;
        }

        $value = str_replace(',', '', $value);

        if (str_contains($value, static::getSystemDecimalPoint())) {
            $result = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        } else {
            $result = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        }

        if ($rate > 1 && is_numeric($result)) {
            $result /= $rate;
        }

        return $result;
    }

    public static function int(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = str_replace(',', '', $value);
        return filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    }

    public static function format(mixed $value, int $decimals = 2, ?string $decimalSeparator = '.', ?string $thousandsSeparator = ','): ?string
    {
        if ($value === null) {
            return null;
        }

        $number = static::numeric($value);
        if ($number === null) {
            return null;
        }

        return number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }

    public static function percent(string $percentage): ?float
    {
        if ($percent = explode('%', $percentage)[0]) {
            return static::numeric($percent) / 100.00;
        }

        return null;
    }
}