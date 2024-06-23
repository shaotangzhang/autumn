<?php
/**
 * Autumn PHP Framework
 *
 * Date:        1/03/2024
 */

namespace Autumn\I18n;

/**
 * Class Locale
 *
 * The Locale class provides internationalization (i18n) and localization (l10n) functionality,
 * allowing for translation and formatting of messages in different languages and regions.
 *
 * @package Autumn\I18n
 */
class Locale extends \Locale
{
    public const DEFAULT_DOMAIN = 'default';
    public const DEFAULT_ERROR_DOMAIN = 'errors';
    public const DEFAULT_FORMAT_TIME = 'H:i:s';
    public const DEFAULT_FORMAT_DATE = 'Y-m-d';
    public const DEFAULT_FORMAT_DATETIME = 'Y-m-d H:i:s';
    public const DEFAULT_DECIMAL_SEPARATOR = '.';
    public const DEFAULT_THOUSANDS_SEPARATOR = ',';

    /** @var array Translations storage */
    private static array $translations = [];

    public static function context(): static
    {
        static $instances = [];
        return $instances[static::class] ??= new static;
    }

    /**
     * Get the translated version of the given text.
     *
     * @param string $text The text to be translated.
     * @param string|null $domain The translation domain. Defaults to the default domain.
     * @param string|\Stringable|null $default The default value to return if the translation is not found.
     *
     * @return string|null The translated text.
     */
    public static function getTranslation(string $text, string $domain = null, string|\Stringable $default = null): ?string
    {
        return self::$translations[$domain ?: static::DEFAULT_DOMAIN][$text] ?? ((func_num_args() > 2) ? $default : $text);
    }

    /**
     * Set the translation for the given text.
     *
     * @param string $text The text to be translated.
     * @param string|\Stringable|null $translation The translated text. If null, the translation is removed.
     * @param string|null $domain The translation domain. Defaults to the default domain.
     *
     * @return void
     */
    public static function setTranslation(string $text, string|\Stringable $translation = null, string $domain = null): void
    {
        if ($translation === null) {
            unset(self::$translations[$domain ?: static::DEFAULT_DOMAIN][$text]);
        } else {
            self::$translations[$domain ?: static::DEFAULT_DOMAIN][$text] = $translation;
        }
    }

    /**
     * Set translations from a data array.
     *
     * @param string $domain The translation domain.
     * @param iterable $data The data array containing translations.
     * @param string|null $prefix A prefix to prepend to translation keys.
     *
     * @return void
     */
    public static function setTranslations(string $domain, iterable $data, string $prefix = null): void
    {
        foreach ($data as $key => $value) {
            if (is_iterable($value)) {
                self::setTranslations($domain, $value, $prefix . $key . '.');
            } else {
                self::setTranslation($prefix . $key, $value, $domain);
            }
        }
    }

    /**
     * Load translations from an INI file.
     *
     * @param string $domain The translation domain.
     * @param string $file The path to the INI file.
     *
     * @return bool True if translations were loaded successfully, false otherwise.
     */
    public static function loadFromIniFile(string $domain, string $file): bool
    {
        if (is_file($file) && is_readable($file)) {
            $data = parse_ini_file($file, true);
            if (is_array($data)) {
                self::setTranslations($domain, $data);
                return true;
            }
        }
        return false;
    }

    /**
     * Format a translated text with sprintf.
     *
     * @param string $text The translated text to format.
     * @param mixed ...$args The values to replace placeholders in the translated text.
     *
     * @return string The formatted text.
     */
    public static function format(string $text, mixed ...$args): string
    {
        $text = static::getTranslation($text);
        return vsprintf($text, $args);
    }

    /**
     * Translate a text with optional replacements.
     *
     * @param string $text The text to translate.
     * @param array|null $args An associative array of replacements for placeholders in the text.
     * @param string|null $domain The translation domain. Defaults to the default domain.
     *
     * @return string The translated and replaced text.
     */
    public static function translate(string $text, array $args = null, string $domain = null): string
    {
        if ($domain) {
            $text = static::getTranslation($text, $domain);
        }

        if ($args && array_is_list($args)) {
            return vsprintf($text, $args);
        }

        if (empty($args)) {
            return $text;
        }

        $params = [];
        foreach ($args as $name => $value) {
            $name = trim($name, '{}');
            $params["{{$name}}"] = (string)$value;
        }

        return strtr($text, $params);
    }

    /**
     * Format an error message using the error domain.
     *
     * @param string $message The error message key.
     * @param mixed ...$args The values to replace placeholders in the error message.
     *
     * @return string The formatted error message.
     */
    public static function error(string $message, mixed ...$args): string
    {
        return static::format(
            static::getTranslation($message, static::DEFAULT_ERROR_DOMAIN . '/' . static::DEFAULT_DOMAIN),
            ...$args
        );
    }

    /**
     * Translate an exception message with optional replacements.
     *
     * @param string $message The exception message key.
     * @param array|null $args An associative array of replacements for placeholders in the exception message.
     * @param string|null $domain The translation domain. Defaults to the default domain.
     *
     * @return string The translated and replaced exception message.
     */
    public static function exception(string $message, array $args = null, string $domain = null): string
    {
        return static::translate(
            $message,
            $args,
            static::DEFAULT_ERROR_DOMAIN . '/' . ($domain ?: static::DEFAULT_DOMAIN)
        );
    }

    /**
     * @throws \Exception
     */
    private static function __to_datetime__(int|float|string|\DateTimeInterface $dateTime = null): \DateTimeInterface
    {
        if ($dateTime === null) {
            return new \DateTimeImmutable;
        }

        if ($dateTime instanceof \DateTimeInterface) {
            return $dateTime;
        }

        if (is_numeric($dateTime)) {
            $dateTime = '@' . $dateTime;
        }

        return new \DateTimeImmutable($dateTime);
    }

    public static function getLocaleFormat(string $format, string $locale = null, string $default = null): ?string
    {
        $domain = strtolower($locale ?: 'locale') . '/formats';
        return static::getTranslation(strtoupper($format), $domain, $default);
    }

    /**
     * @throws \Exception
     */
    public static function formatLocaleTime(int|float|string|\DateInterval|\DateTimeInterface $dateTime = null, string $locale = null, string $format = null): string
    {
        if ($dateTime === null) {
            return '';
        }

        $localeFormat = static::getLocaleFormat($format ?: 'TIME', $locale, $format ?: static::DEFAULT_FORMAT_TIME);

        if ($dateTime instanceof \DateInterval) {
            $localeFormat = strtr($localeFormat, ['H' => '%H', 'i' => '%I', 's' => '%S']);
            return $dateTime->format($localeFormat);
        } else {
            $time = self::__to_datetime__($dateTime);
            return $time->format($localeFormat);
        }
    }

    /**
     * @throws \Exception
     */
    public static function formatLocaleDate(int|float|string|\DateTimeInterface $dateTime = null, string $locale = null, string $format = null): string
    {
        if ($dateTime === null) {
            return '';
        }

        $time = self::__to_datetime__($dateTime);
        $localeFormat = static::getLocaleFormat($format ?: 'DATE', $locale, $format ?: static::DEFAULT_FORMAT_DATE);
        return $time->format($localeFormat);
    }

    /**
     * @throws \Exception
     */
    public static function formatLocaleDateTime(int|float|string|\DateTimeInterface $dateTime = null, string $locale = null, string $format = null): string
    {
        if ($dateTime === null) {
            return '';
        }

        $time = self::__to_datetime__($dateTime);
        $localeFormat = static::getLocaleFormat($format ?: 'DATETIME', $locale, $format ?: static::DEFAULT_FORMAT_DATETIME);
        return $time->format($localeFormat);
    }

    /**
     * Get information about the current or provided locale's number formatting.
     *
     * @param string|null $key The specific information key to retrieve. If not provided, returns all information.
     * @param mixed $default The default value to return if the requested key is not found.
     *
     * @return mixed The requested locale information or all locale information if the key is not provided.
     */
    public static function localeconv(string $key = null, mixed $default = null): mixed
    {
        static $localeConv;

        // Lazy initialization of localeconv data
        if ($localeConv === null) {
            if (function_exists('localeconv')) {
                $localeConv = localeconv();
            } else {
                $localeConv = [];
            }
        }

        // Return specific key or the entire localeconv data
        if (func_num_args()) {
            return $localeConv[$key] ?? $default;
        }

        return $localeConv;
    }

    /**
     * Get the decimal separator for the specified or current locale.
     *
     * @param string|null $locale The specific locale for which to retrieve the decimal separator.
     *                             If not provided, the current locale's decimal separator will be used.
     *
     * @return string The decimal separator for the specified or current locale.
     */
    public static function getLocaleDecimalSeparator(string $locale = null): string
    {
        static $decimalSeparator;

        if (func_num_args()) {
            return static::getLocaleFormat('decimal_point', $locale)
                ?? ($locale ? '' : static::getLocaleDecimalSeparator());
        }

        return $decimalSeparator ??= static::localeconv('decimal_point') ?? '.';
    }

    /**
     * Get the thousands separator for the specified or current locale.
     *
     * @param string|null $locale The specific locale for which to retrieve the thousands separator.
     *                             If not provided, the current locale's thousands separator will be used.
     *
     * @return string The thousands separator for the specified or current locale.
     */
    public static function getLocaleThousandsSeparator(string $locale = null): string
    {
        static $thousandsSeparator;

        if (func_num_args()) {
            return static::getLocaleFormat('thousands_sep', $locale)
                ?? ($locale ? '' : static::getLocaleDecimalSeparator());
        }

        if (empty($thousandsSeparator ??= static::localeconv('thousands_sep'))) {
            $thousandsSeparator = (static::getLocaleDecimalSeparator() === '.') ? ',' : '.';
        }

        return $thousandsSeparator;
    }

    public static function getLocaleCurrencySymbol(string $locale = null): string
    {
        static $localeCurrency;

        if (func_num_args()) {
            return static::getLocaleFormat('currency_symbol', $locale)
                ?: ($locale ? '' : static::getLocaleCurrencySymbol());
        }

        return $localeCurrency ??= static::localeconv('currency_symbol') ?? '';
    }

    public static function getLocaleCurrencyDecimalPoint(string $locale = null): string
    {
        static $localeCurrencyDecimalPoint;

        if (func_num_args()) {
            return static::getLocaleFormat('mon_decimal_point', $locale)
                ?: ($locale ? '' : static::getLocaleCurrencyDecimalPoint());
        }

        return $localeCurrencyDecimalPoint ??= (
            static::localeconv('mon_decimal_point') ?? static::getLocaleDecimalSeparator()
        );
    }

    public static function getLocaleCurrencyThousandsSeparator(string $locale = null): string
    {
        static $localeCurrencyThousandsSeparator;

        if (func_num_args()) {
            return static::getLocaleFormat('mon_thousands_sep', $locale)
                ?: ($locale ? '' : static::getLocaleCurrencyThousandsSeparator());
        }

        return $localeCurrencyThousandsSeparator ??= (
            static::localeconv('mon_thousands_sep') ?? static::getLocaleThousandsSeparator()
        );
    }

    private static function __to_amount__(int|float|string $amount = null): int|float|null
    {
        if (!isset($amount) || is_numeric($amount)) {
            return $amount;
        }

        return filter_var(strtr($amount, ',', ''), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    }

    public static function formatAmount(int|float|string $amount = null, int $decimals = null, string $locale = null): string
    {
        $amount = static::__to_amount__($amount);
        if ($amount === null) {
            return '';
        }

        $decimalSeparator = static::getLocaleDecimalSeparator($locale) ?: '.';
        $thousandsSeparator = static::getLocaleThousandsSeparator($locale) ?: (($decimalSeparator === '.') ? ',' : '.');
        return number_format($amount, $decimals ?? 0, $decimalSeparator, $thousandsSeparator);
    }

    public static function formatCurrency(int|float|string $amount = null, int $decimals = null, string $currency = null, string $locale = null, string $spaceBetweenCurrencyAndAmount = null): string
    {
        $amount = static::__to_amount__($amount);
        if ($amount === null) {
            return '';
        }

        $currency ??= static::getLocaleCurrencySymbol($locale);

        return $currency . $spaceBetweenCurrencyAndAmount
            . number_format($amount, $decimals,
                static::getLocaleCurrencyDecimalPoint($locale) ?: '.',
                static::getLocaleCurrencyThousandsSeparator($locale)
            );
    }
}