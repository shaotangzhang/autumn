<?php

namespace Autumn\I18n;

use Autumn\App;
use Traversable;

class Translation implements \IteratorAggregate
{
    private static array $translations = [
        // domain => [
        // key => value
        // ]
    ];

    private array $messages = [];

    public function __construct(private readonly string $domain = '', private readonly string $lang = '')
    {
    }

    public static function forClass(string $class, string $lang = null): static
    {
        return new static($class, $lang ?? '');
    }

    /**
     * Loads translations for a specified domain, optionally with a prefix, and language.
     *
     * @param string $domain The translation domain to load.
     * @param string|null $prefix An optional prefix to add to keys.
     * @param string|null $lang The language code (optional). If not provided, uses the default language.
     * @return static Returns a Translation instance with loaded translations.
     */
    public static function load(string $domain, string $prefix = null, string $lang = null): static
    {
        // Determine language to use
        $lang ??= static::lang();

        // If translations for the domain in the specified language are not loaded yet
        if (!isset(self::$translations[$lang][$domain])) {
            // Initialize translations array for the domain and language
            self::$translations[$lang][$domain] = [];

            // Attempt to load translations from a language file
            if (realpath($file = App::context()->getLanguageFile($domain, $lang))) {
                // Parse the language file (assuming it's in INI format)
                $data = parse_ini_file($file, true);

                // If data is iterable (translations found in the file)
                if (is_iterable($data)) {
                    // Create a temporary Translation instance for merging data
                    $temp = new static($domain, $lang);
                    $temp->merge($data); // Merge parsed translations into the temporary instance
                    self::$translations[$lang][$domain] = $temp->messages; // Store merged translations

                    // If no prefix is specified, return the temporary instance
                    if (!$prefix) {
                        return $temp;
                    }
                }
            }
        }

        // Create a new Translation instance for the domain and language
        $instance = new static($domain, $lang);

        if ($prefix) {
            // Merge existing translations for the domain and language with optional prefix
            $instance->merge(self::$translations[$lang][$domain], $prefix);
        }

        // Return the created instance with loaded translations
        return $instance;
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

    public static function global(Translation $translation = null): ?static
    {
        static $g;

        if (func_num_args()) {
            $current = $g;
            $g = $translation;
            return $current;
        }

        return $g;
    }

    public function format(string $text, mixed ...$args): ?string
    {
        if ($format = $this->messages[$text] ?? static::$translations[$this->lang ?: static::lang()][$this->domain][$text] ?? null) {
            if ($args) {
                return vsprintf($format, $args);
            }

            return $format;
        }

        return null;
    }

    public function translate(string $text, array $args, string $lang = null): string
    {
        if ($format = $this->messages[$text] ?? static::$translations[($lang ?? $this->lang) ?: static::lang()][$this->domain][$text] ?? null) {
            if ($args) {
                return vsprintf($format, $args);
            }

            return $format;
        }

        return $text;
    }

    public function reset(): void
    {
        $this->messages = [];
    }

    public function merge(iterable $translations, string $prefix = null): static
    {
        foreach ($translations as $name => $message) {
            if (is_iterable($message)) {
                $this->merge($message, $prefix . $name . '.');
            } else {
                $this->messages[$prefix . $name] = $message;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->messages);
    }
}