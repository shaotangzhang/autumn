<?php
/**
 * Autumn PHP Framework
 *
 * Date:        9/01/2024
 */

namespace Autumn\Lang;

class Date
{
    public const INPUT_DATE = 'Y-m-d';
    public const INPUT_TIME = 'H:i:s';
    public const INPUT_TIME_SHORT = 'H:i';
    public const INPUT_DATETIME_LOCAL = 'Y-m-d\TH:i:s';
    public const INPUT_DATETIME_SHORT = 'Y-m-d\TH:i';
    public const INPUT_WEEK = 'Y-\WW';
    public const INPUT_MONTH = 'Y-m';
    public const INPUT_YEAR = 'Y';

    public static function of(int|float|string|\DateTimeInterface $time, string|\DateTimeZone $timeZone = null): \DateTimeInterface
    {
        if (is_numeric($time)) {
            $time = '@' . $time;
        } elseif ($time instanceof \DateTimeInterface) {
            return $time;
        }

        try {
            if (is_string($timeZone)) {
                $timeZone = new \DateTimeZone($timeZone);
            }

            return new \DateTimeImmutable($time, $timeZone);
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public static function tryFrom(mixed $time, string|\DateTimeZone $timeZone = null): ?\DateTimeInterface
    {
        if ($time instanceof \DateTimeInterface) {
            return $time;
        }

        if (is_numeric($time)) {
            $time = '@' . $time;
        } elseif (!is_string($time)) {
            return null;
        }

        try {
            if (is_string($timeZone)) {
                $timeZone = new \DateTimeZone($timeZone);
            }

            return new \DateTimeImmutable($time, $timeZone);
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function utc(int|float|string|\DateTimeInterface $time = null): ?\DateTimeInterface
    {
        if (is_numeric($time)) {
            $time = '@' . $time;
        } elseif ($time instanceof \DateTimeInterface) {
            return $time;
        }

        try {
            $timeZone = new \DateTimeZone('UTC');
            return new \DateTimeImmutable($time ?? 'now', $timeZone);
        } catch (\Exception) {
            return null;
        }
    }

    public static function now(): \DateTimeInterface
    {
        return new \DateTimeImmutable;
    }

    public static function timestamp(int|float|string|\DateTimeInterface $time): int|float|false
    {
        if (is_int($time)) {
            return $time;
        }

        if (is_numeric($time)) {
            return (float)$time;
        }

        if (is_string($time)) {
            return strtotime($time);
        }

        return $time->getTimestamp();
    }

    public static function format(string $format, int|float|string|\DateTimeInterface $time = null): string
    {
        return date($format, isset($time) ? static::timestamp($time) : time());
    }

    public static function json(\DateTimeInterface $data): mixed
    {
        return $data->format('c');
    }

    public static function offset(\DateInterval|float|int $offset, \DateTimeInterface $time = null): \DateTimeInterface
    {
        if ($offset instanceof \DateInterval) {
            return ($time ?? new \DateTimeImmutable)->add($offset);
        } else {
            return static::of((microtime(true) + $offset));
        }
    }

    public static function fromInput(int|float|string|\DateTimeInterface|null $value, string|\DateTimeZone $timeZone = null): ?\DateTimeInterface
    {
        if ($value === null || $value === '' || $value === '0' || $value === 0 || $value === 0.0) {
            return null;
        }

        return static::of($value, $timeZone);
    }

    public static function forInputValue(int|float|string|\DateTimeInterface|null $time, string $type, int|float|string|\DateTimeInterface|null $default = null): ?string
    {
        if (!static::hasInputType($type)) {
            return null;
        }

        $value = $time ? static::timestamp($time) : false;
        if (!$value) {
            if ($default !== null) {
                $value = static::timestamp($default);
                if (!$value) {
                    return null;
                }
            }
            return null;
        }

        return match (strtolower($type)) {
            'time' => date(static::INPUT_TIME, $value),
            'time-short' => date(static::INPUT_TIME_SHORT, $value),
            'date' => date(static::INPUT_DATE, $value),
            //'datetime' => date(static::INPUT_DATETIME_SHORT, $value),
            'datetime-local' => date(static::INPUT_DATETIME_SHORT, $value),
            'week' => date(static::INPUT_WEEK, $value),
            'month' => date(static::INPUT_MONTH, $value),
            'year' => date(static::INPUT_YEAR, $value),
            default => date($type, $value),
        };
    }

    public static function hasInputType(string $type): string
    {
        return match ($type = strtolower($type)) {
            'time', 'date', 'datetime-local', 'week', 'month', 'year' => $type,
            'datetime' => 'datetime-local',
            'time-short' => 'time',
            default => '',
        };
    }

    public static function interval(mixed $duration): \DateInterval
    {
        // 如果 $duration 已经是 DateInterval 类型，直接返回
        if ($duration instanceof \DateInterval) {
            return $duration;
        }

        // 如果 $duration 是整数，将其解释为秒数
        if (is_int($duration) || is_float($duration)) {
            $durationInSeconds = intval($duration);
            try {
                return new \DateInterval('PT' . $durationInSeconds . 'S');
            } catch (\Exception $e) {
                throw new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
            }
        }

        // 如果 $duration 是字符串，尝试解析为 ISO8601 时间间隔格式
        if (is_string($duration)) {
            try {
                return new \DateInterval($duration);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException($e->getMessage() ?: ('Invalid duration format: ' . $duration), $e->getCode(), $e);
            }
        }

        // 如果 $duration 是一个可转换为整数的对象，尝试将其转换为整数
        if (is_object($duration) && method_exists($duration, '__toString')) {
            $duration = (int)$duration;
            try {
                return new \DateInterval('PT' . $duration . 'S');
            } catch (\Exception $e) {
                throw new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
            }
        }

        throw new \InvalidArgumentException('Unsupported duration type: ' . gettype($duration));
    }

    public static function seconds(int|string|\DateInterval|\DateTimeInterface $value): int|float
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            try {
                if (str_starts_with($value, 'P')) {
                    $value = new \DateInterval($value);
                } else {
                    $value = new \DateTimeImmutable($value);
                }
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid time value provided.', $e->getCode(), $e);
            }
        }

        if ($value instanceof \DateInterval) {
            return abs((new \DateTimeImmutable)->add($value)->getTimestamp() - time());
        }

        return (float)$value->format('U.u');
    }

    public static function add(int|\DateInterval $interval, int|float|string|\DateTimeInterface $time = null): \DateTimeInterface
    {
        if (is_int($interval)) {
            $timestamp = $time ? static::timestamp($time) : time();
            return static::of($timestamp + $interval);
        }

        return ($time ? Date::of($time) : new \DateTimeImmutable)->add($interval);
    }

    public static function isExpired(int|\DateInterval $ttl, int|float|string|\DateTimeInterface $time = null): int
    {
        return static::add($ttl, $time)->getTimestamp() < time();
    }

    public static function floor(int|float|string|\DateTimeInterface $time = null, int|\DateInterval $interval = null, \DateTimeZone $timeZone = null): ?\DateTimeInterface
    {
        if ($interval = static::seconds($interval)) {
            $time = $time ? static::seconds($time) : time();
            $time = floor($time / $interval) * $interval;
        }

        return $time ? static::of($time) : new \DateTimeImmutable;
    }

    public static function round(int|float|string|\DateTimeInterface $time = null, int|\DateInterval $interval = null, \DateTimeZone $timeZone = null): ?\DateTimeInterface
    {
        if ($interval = static::seconds($interval)) {
            $time = $time ? static::seconds($time) : time();
            $time = round($time / $interval) * $interval;
        }

        return $time ? static::of($time) : new \DateTimeImmutable;
    }

    public static function ceil(int|float|string|\DateTimeInterface $time = null, int|\DateInterval $interval = null, \DateTimeZone $timeZone = null): ?\DateTimeInterface
    {
        if ($interval = static::seconds($interval)) {
            $time = $time ? static::seconds($time) : time();
            $time = ceil($time / $interval) * $interval;
        }

        return $time ? static::of($time) : new \DateTimeImmutable;
    }

    public static function encode(\DateTimeInterface $result): string
    {
        return $result->format('c');
    }

    public static function toDuration(int|float|\DateInterval|string|null $duration, bool $showHours = false, bool $showMilli = false): ?string
    {
        if ($duration === null) {
            return null;
        }

        $hours = 0;
        $minutes = 0;
        $seconds = 0;
        $milliseconds = 0;

        if (is_numeric($duration)) {
            // Handle numeric input (interpreted as seconds, possibly with fractional part)
            $totalSeconds = (float)$duration;
            $milliseconds = ($totalSeconds - floor($totalSeconds)) * 1000;
            $totalSeconds = (int)$totalSeconds;

            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);
            $seconds = $totalSeconds % 60;
        } elseif ($duration instanceof \DateInterval) {
            // Handle DateInterval input
            $hours = $duration->h + $duration->d * 24 + $duration->m * 30 * 24 + $duration->y * 365 * 24; // Approximation for months and years
            $minutes = $duration->i;
            $seconds = $duration->s;
            $milliseconds = (int)($duration->f * 1000); // DateInterval has microseconds as fraction
        } elseif (is_string($duration)) {
            // Handle string input (interpreted as a relative time string)
            try {
                $now = new \DateTime();
                $future = new \DateTime($duration);
                $interval = $now->diff($future);

                $hours = $interval->h + $interval->d * 24 + $interval->m * 30 * 24 + $interval->y * 365 * 24; // Approximation for months and years
                $minutes = $interval->i;
                $seconds = $interval->s;
                $milliseconds = (int)($interval->f * 1000); // DateInterval has microseconds as fraction
            } catch (\Exception) {
                return null;
            }
        } else {
            return null;
        }

        // Construct the duration string based on the flags
        $durationString = sprintf('%02d:%02d', $minutes, $seconds);

        if ($showHours) {
            $durationString = sprintf('%02d:', $hours) . $durationString;
        }

        if ($showMilli) {
            $durationString .= sprintf('.%03d', $milliseconds);
        }

        return $durationString;
    }


}