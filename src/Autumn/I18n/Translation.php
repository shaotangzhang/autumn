<?php
/**
 * Autumn PHP Framework
 *
 * Date:        21/05/2024
 */

namespace Autumn\I18n;

class Translation
{
    private static array $translations = [
        // domain => [
        // key => value
        // ]
    ];

    public function __construct(private readonly string $domain = '', private readonly string $lang = '')
    {
    }

    public static function forClass(string $class, string $lang = null): static
    {
        return new static($class, $lang ?? '');
    }

    public static function lang(): string
    {
        static $lang;

        return $lang ??= (env('SITE_LANG')
            ?: env('LANGUAGE')
                ?: (extension_loaded('intl')
                    ? \Locale::getDefault()
                    : substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2)
                )
        );
    }

    public function format(string $text, mixed ...$args): ?string
    {
        if ($format = static::$translations[$this->lang ?: static::lang()][$this->domain][$text] ?? null) {
            if ($args) {
                return vsprintf($format, $args);
            }

            return $format;
        }

        return null;
    }

    public function translate(string $text, array $args, string $lang = null): string
    {
        if ($format = static::$translations[($lang ?? $this->lang) ?: static::lang()][$this->domain][$text] ?? null) {
            if ($args) {
                return vsprintf($format, $args);
            }

            return $format;
        }

        return $text;
    }
}