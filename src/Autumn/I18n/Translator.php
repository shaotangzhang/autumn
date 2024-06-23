<?php

namespace Autumn\I18n;

use Autumn\App;
use Autumn\Exceptions\ValidationException;
use Autumn\System\Reflection;
use InvalidArgumentException;

/**
 * Translator class handles internationalization and translation functionality.
 */
class Translator implements TranslatorInterface
{
    public const PLACEHOLDER_PATTERN = '/%(?:\d+\$)?[+-]?(?:[ 0])?(?:\'[^\']|[0-9]+)?(?:\.[0-9]+)?[bcdeEfFgGosuxX]/';
    public const LANGUAGE_FILE_EXT = '.conf';
    public const DEFAULT_LANGUAGE_NAME = 'default';
    private string $filePath = '';
    private string $fileName = '';
    private string $language = '';
    private array $translations = [];

    /**
     * Creates a Translator instance for a specific class and language.
     *
     * @param string $class The class name.
     * @param string|null $lang Optional. The language code to use. Defaults to system default.
     * @return static The Translator instance.
     */
    public static function forClass(string $class, string $lang = null): static
    {
        static $cache;

        $lang = $lang ?? static::lang() ?: 'default';

        if (isset($cache[$class][$lang])) {
            return $cache[$cache][$lang];
        }

        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf(
                'The class `%s` is not found.', $class
            ));
        }

        $reflection = new \ReflectionClass($class);
        while ($reflection->isAnonymous()) {
            $reflection = $reflection->getParentClass();
            if (!$reflection) {
                $reflection = null;
                break;
            }
        }

        $baseClass = $reflection?->getName() ?: $class;
        if (isset($cache[$baseClass][$lang])) {
            return $cache[$baseClass][$lang];
        }

        $instance = new static;
        $instance->setLanguage($lang);
        $instance->loadForClass($baseClass);
        return $cache[$class][$lang] = $cache[$baseClass][$lang] = $instance;
    }

    /**
     * Checks if a language is supported.
     *
     * @param string $language The language code to check.
     * @return bool True if the language is supported, false otherwise.
     */
    public static function isSupportedLanguage(string $language): bool
    {
        return static::hasSupportedLanguage($language) !== false;
    }

    /**
     * Checks if a language is supported and returns its path if available.
     *
     * @param string $language The language code to check.
     * @return false|string The path to the language file if supported, false otherwise.
     */
    public static function hasSupportedLanguage(string $language): false|string
    {
        static $paths;

        return $paths[$language] ??= realpath(static::getLanguageFilePath($language));
    }

    /**
     * Returns the path to a language file
     *
     * @param string|null $language
     * @return string
     */
    public static function getLanguageFilePath(string $language = null): string
    {
        return App::path('languages', $language ?? static::DEFAULT_LANGUAGE_NAME);
    }

    /**
     * Detects the best supported language based on the Accept-Language header.
     *
     * @return string The detected language code.
     */
    public static function detectAcceptLanguage(): string
    {
        static $lang;

        if ($lang !== null) {
            return $lang;
        }

        $lang = '';
        foreach (static::acceptLanguages() as $language => $weight) {
            if (static::isSupportedLanguage($language)) {
                return $lang = $language;
            }
        }

        return $lang;
    }

    /**
     * Parses the Accept-Language header into an array of languages with weights.
     *
     * @return array An associative array of languages and their weights.
     */
    public static function acceptLanguages(): array
    {
        static $acceptLanguages;

        return $acceptLanguages ??= (($acceptLanguageHeader = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null)
            ? static::parseAcceptLanguages($acceptLanguageHeader)
            : []
        );
    }

    /**
     * Parses the Accept-Language header string into an array of languages with weights.
     *
     * @param string $acceptLanguageHeader The Accept-Language header string.
     * @return array An associative array of languages and their weights.
     */
    public static function parseAcceptLanguages(string $acceptLanguageHeader): array
    {
        $languages = [];
        $languageRanges = explode(',', $acceptLanguageHeader);

        foreach ($languageRanges as $languageRange) {
            $parts = explode(';q=', $languageRange);
            $language = trim($parts[0]);
            $weight = isset($parts[1]) ? (float)$parts[1] : 1.0;
            $languages[$language] = $weight;
        }

        arsort($languages, SORT_NUMERIC);

        return $languages;
    }

    /**
     * Retrieves the default language from environment or system settings.
     *
     * @return string|null The default language code, or null if not found.
     */
    public static function defaultLanguage(): ?string
    {
        return env('SITE_LANG') ?: env('LANGUAGE')
            ?: (extension_loaded('intl') ? \Locale::getDefault() : null);
    }

    /**
     * Retrieves the current language code based on Accept-Language header or default.
     *
     * @return string|null The detected language code, or null if not detected.
     */
    public static function lang(): ?string
    {
        static $lang;
        return $lang ??= static::detectAcceptLanguage() ?: static::defaultLanguage();
    }

    /**
     * Extracts placeholders from a format string.
     *
     * @param string $format The format string possibly containing placeholders.
     * @param mixed ...$args Optional. Arguments to be replaced into placeholders.
     * @return array An array of placeholders with their associated values.
     */
    public static function getPlaceholders(string $format, mixed ...$args): array
    {
        if (preg_match_all(self::PLACEHOLDER_PATTERN, $format, $matches)) {
            $placeholders = [];
            foreach ($matches[0] as $index => $placeholder) {
                $placeholders[] = [
                    'placeholder' => $placeholder,
                    'value' => $args[$index] ?? null
                ];
            }

            return $placeholders;
        }

        return [];
    }

    /**
     * Prepares placeholders from a format string for vsprintf.
     *
     * @param string $format The format string possibly containing placeholders.
     * @param array|null $args Optional. Arguments to be replaced into placeholders.
     * @return array An array of values to be used with vsprintf.
     */
    public static function preparePlaceholders(string $format, array $args = null): array
    {
        if (preg_match_all(self::PLACEHOLDER_PATTERN, $format, $matches)) {
            $placeholders = [];
            foreach ($matches[0] as $index => $placeholder) {
                $placeholders[] = $args[$index] ?? null;
            }
            return $placeholders;
        }

        return [];
    }

    /**
     * Formats a string with placeholders replaced by given arguments.
     *
     * @param string $format The format string with placeholders.
     * @param mixed ...$args Arguments to replace into placeholders.
     * @return string The formatted string.
     */
    public static function print(string $format, mixed ...$args): string
    {
        if ($placeholders = static::preparePlaceholders($format, $args)) {
            return vsprintf($format, $placeholders);
        }

        return $format;
    }

    /**
     * Returns the name of the language file
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Sets the name of the language file
     *
     * @param string $fileName
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * Returns the file path to the language files
     *
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath ??= App::path('languages');
    }

    /**
     * Sets the file path to the language files
     *
     * @param string $filePath
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = rtrim($filePath, '/\\');
    }

    /**
     * Retrieves the current set language of the Translator instance.
     *
     * @return string The current language code.
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Sets the language for the Translator instance.
     *
     * @param string $language The language code to set.
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    /**
     * Retrieves the translation of a text in the current or specified language.
     *
     * @param string $text The text to translate.
     * @param string|null $language Optional. The language code to retrieve the translation for.
     * @return string|null The translated text, or null if not found.
     */
    public function getTranslation(string $text, string $language = null): ?string
    {
        return $this->translations[$language ?? $this->getLanguage()][$text] ?? null;
    }

    /**
     * Sets a translation for a text in the current or specified language.
     *
     * @param string $text The text to set the translation for.
     * @param string|null $format The translation format string.
     * @param string|null $language Optional. The language code to set the translation for.
     */
    public function setTranslation(string $text, string $format = null, string $language = null): void
    {
        $this->translations[$language ?? $this->getLanguage()][$text] = $format;
    }

    /**
     * Loads translations into the current Translator instance.
     *
     * @param array $translations The translations to load.
     * @param string|null $language Optional. The language code to load translations for.
     * @param string|null $prefix Optional. A prefix to prepend to translation keys.
     */
    public function load(array $translations, string $language = null, string $prefix = null): void
    {
        $lang = $language ?? $this->getLanguage();
        foreach ($translations as $section => $values) {
            if (is_array($values)) {
                $this->load($values, $lang, $prefix . $section . '.');
            } else {
                $this->translations[$lang][$prefix . $section] = $values;
            }
        }
    }

    public function loadFromTranslator(self $translator, string $prefix = null): void
    {

    }

    /**
     * Loads translations for a specific class into the current Translator instance.
     *
     * @param string $class The class name to load translations for.
     * @param string|null $language Optional. The language code to load translations for.
     */
    public function loadForClass(string $class, string $language = null): void
    {
        if (class_exists($class)) {
            // Convert class name to a file path
            $name = strtr(trim($class, '/\\'), '\\', DIRECTORY_SEPARATOR);
            $file = App::path('languages', $language ?: $this->getLanguage() ?: 'default', $name . static::LANGUAGE_FILE_EXT);
            // Load translations from the INI file
            $this->loadFromINI($file);
        }
    }

    /**
     * Loads translations from an INI file into the current Translator instance.
     *
     * @param string $file The path to the INI file.
     * @param string|null $language Optional. The language code to load translations for.
     * @param string|null $prefix Optional. A prefix to prepend to translation keys.
     */
    public function loadFromINI(string $file, string $language = null, string $prefix = null): void
    {
        if (is_file($file)) {
            // Parse the INI file
            $data = parse_ini_file($file, true);
            if (is_array($data)) {
                // Load the parsed data as translations
                $this->load($data, $language, $prefix);
            }
        }
    }

    /**
     * Formats a string with placeholders replaced by given arguments.
     *
     * @param string $text The text to format.
     * @param array|null $args Optional. Arguments to replace into placeholders.
     * @param string|null $language Optional. The language code for translation.
     * @param string|null $domain Optional. The translation domain.
     * @return string|null The formatted string, or null if not found.
     */
    public function format(string $text, array $args = null, string $language = null, string $domain = null): ?string
    {
        if ($domain) {
            $text = "$domain.$text";
        }

        if ($format = $this->getTranslation($text, $language)) {
            if ($placeholders = static::preparePlaceholders($format, $args)) {
                return vsprintf($format, $placeholders);
            }
            return $format;
        }

        return null;
    }

    /**
     * Translates a text with optional sprintf-style formatting.
     *
     * @param string $text The text to translate.
     * @param mixed ...$args Optional. Arguments to replace into placeholders.
     * @return string|null The translated string, or null if not found.
     */
    public function translate(string $text, mixed ...$args): ?string
    {
        if ($format = $this->getTranslation($text)) {
            if ($placeholders = static::preparePlaceholders($format, $args)) {
                return vsprintf($format, $placeholders);
            }
            return $format;
        }

        return null;
    }

    /**
     * Convert the translations in one language into a string in INI format.
     *
     * This method converts the translations stored in the $this->translations array
     * for a specified language into an INI formatted string representation.
     *
     * @param string|null $language The language code of the translations to convert.
     * @return string The translations in INI format as a string.
     */
    public function toINI(string $language = null): string
    {
        $lines = [];

        // Iterate through each section of translations for the specified language
        foreach ($this->translations[$language ?? $this->getLanguage()] ?? [] as $section => $values) {
            // If $values is an array, it means there are multiple translation entries under this section
            if (is_array($values)) {
                // Add section header in INI format
                $lines[] = "[$section]";
                // Iterate through each translation entry in the section
                foreach ($values as $text => $format) {
                    // Convert each translation entry to INI format key-value pair
                    $lines[] = $text . ' = ' . json_encode($format);
                }
            } else {
                // If $values is not an array, it represents a single translation entry for the section
                // Convert section and its translation to INI format line
                $lines[] = $section . ' = ' . json_encode($values);
            }
        }

        // Join all lines with PHP_EOL (end of line) to form the final INI formatted string
        return implode(PHP_EOL, $lines);
    }

    /**
     * Save translations for a language to an INI file.
     *
     * @param string|null $language The language code of the translations to save.
     * @return bool True if saving was successful, false otherwise.
     */
    public function save(string $language = null): bool
    {
        // Construct the file path to save the INI file
        $file = $this->getFilePath()
            . DIRECTORY_SEPARATOR . ($language ??= $this->getLanguage())
            . DIRECTORY_SEPARATOR . $this->getFileName() . static::LANGUAGE_FILE_EXT;

        // Delegate saving to saveAs() method
        return $this->saveAs($file, $language);
    }

    /**
     * Save translations for a language to an INI file under a specific path.
     *
     * @param string $path The directory path where the INI file will be saved.
     * @param string|null $language The language code of the translations to save.
     * @return bool True if saving was successful, false otherwise.
     */
    public function saveTo(string $path, string $language = null): bool
    {
        // Construct the file path to save the INI file under the specified path
        $file = rtrim($path, '/\\')
            . DIRECTORY_SEPARATOR . ($language ??= $this->getLanguage())
            . DIRECTORY_SEPARATOR . $this->getFileName() . static::LANGUAGE_FILE_EXT;

        // Delegate saving to saveAs() method
        return $this->saveAs($file, $language);
    }

    /**
     * Save translations for a language to a specific INI file.
     *
     * @param string $file The full path of the INI file to save.
     * @param string|null $language The language code of the translations to save.
     * @return bool True if saving was successful, false otherwise.
     */
    private function saveAs(string $file, string $language = null): bool
    {
        // Convert translations to INI format
        $data = $this->toINI($language);

        // If conversion fails or no data to save, return false
        if (!$data) {
            return false;
        }

        // Ensure directory exists, create if not
        if (!is_dir($path = dirname($file))) {
            if (!mkdir($path, 0777, true)) {
                return false;
            }
        }

        // Write data to the INI file and return true if successful
        return file_put_contents($file, $data) > 0;
    }

}