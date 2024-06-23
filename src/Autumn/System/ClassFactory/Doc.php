<?php

namespace Autumn\System\ClassFactory;

/**
 * Abstract class Doc provides utility functions for normalizing strings and printing formatted code.
 */
abstract class Doc implements \Stringable
{
    // Define the tab character.
    public const TAB = "\t";
    public const EOL = PHP_EOL;
    public const DEFAULT_LINE_WIDTH = 75;

    // Define replacement rules as constants.
    public const STRING_NORMALIZER = [
        '/\r?\n/' => PHP_EOL,  // Normalize all newlines to PHP_EOL.
        '/\t/' => '    '       // Replace tabs with four spaces.
    ];

    // Property to store indentation.
    private string $intent = '';

    /**
     * Get the short name of a class and optionally retrieve its namespace.
     *
     * @param string $className The fully qualified class name.
     * @param string|null $namespace Reference to store the namespace of the class.
     * @return string The short name of the class (without namespace).
     */
    public static function getClassShortName(string $className, ?string &$namespace = null): string
    {
        $pos = strrpos($className, '\\');
        if ($pos !== false) {
            $namespace = substr($className, 0, $pos);
            return substr($className, $pos + 1);
        }
        return $className;
    }

    /**
     * Normalize a class name to ensure it is properly suitable for importing.
     *
     * Removes any leading backslash (`\`) from the beginning of the class name
     * string, ensuring it adheres to importable class name conventions.
     *
     * @param string $className The class name to normalize.
     * @return string The normalized class name without leading backslashes.
     */
    public static function importableClassName(string $className): string
    {
        return ltrim($className, '\\');
    }

    /**
     * Normalize a class name to ensure it is properly namespaced.
     *
     * This method checks if the class name contains namespace separators.
     * If it does, it trims any leading or trailing separators and prepends a single leading separator.
     * If it doesn't, it returns the class name unchanged.
     *
     * @param string $className The class name to normalize.
     * @return string The normalized class name.
     */
    public static function normalizeClassName(string $className): string
    {
        if (empty($className)) {
            return $className;
        }

        if (str_contains($className, '\\')) {
            return '\\' . trim($className, '\\');
        }

        return $className;
    }

    /**
     * Simplify a class name to be relative to a given namespace.
     *
     * @param string $className The normalized class name to simplify.
     * @param string $namespace The normalized base namespace to calculate the relative class name.
     * @return string The simplified relative class name.
     */
    public static function simplifyClassName(string $className, string $namespace): string
    {
        if (empty($className)) {
            return '';
        }

        // Ensure the namespace ends with a backslash
        if (!str_ends_with($namespace, '\\')) {
            $namespace .= '\\';
        }

        // Check if the class name starts with the namespace
        if (str_starts_with($className, $namespace)) {
            return substr($className, strlen($namespace));
        }

        // If the class name does not start with the namespace, return it as-is
        return $className;
    }

    /**
     * Normalize string by replacing all \r\n or \n with PHP_EOL and tabs with four spaces.
     *
     * @param string $text The input string to normalize.
     * @return string The normalized string.
     */
    public static function normalizeString(string $text): string
    {
        return preg_replace(
            array_keys(self::STRING_NORMALIZER),
            array_values(self::STRING_NORMALIZER),
            $text
        );
    }

    /**
     * Normalize a text by replacing line breaks and then wrap lines to a specified line width.
     *
     * This method normalizes line breaks in the given text by converting them to PHP_EOL,
     * and then wraps the text into lines of a specified line width using wordwrap.
     *
     * @param string $text The text to normalize and wrap.
     * @param int $lineWidth The desired line width for wrapping. Default is 75.
     * @return array An array of lines after normalization and wrapping.
     */
    public static function normalizeTextLines(string $text, int $lineWidth = self::DEFAULT_LINE_WIDTH): array
    {
        $text = static::normalizeString($text);
        return explode(PHP_EOL, wordwrap($text, $lineWidth, PHP_EOL));
    }

    /**
     * Print the formatted string with the given indentation, doc comment, and other strings.
     *
     * @param int|string $intent The indentation level or string.
     * @param ?DocComment $docComment The doc comment object.
     * @param mixed ...$others Additional strings to print.
     * @return string The formatted string.
     */
    public static function print(int|string $intent, ?DocComment $docComment, mixed ...$others): string
    {
        if (is_int($intent)) {
            // Convert integer indentation level to tab string.
            $intent = str_repeat(static::TAB, $intent);
        }

        $lines = [];
        if ($docComment) {
            // Set indentation for doc comment.
            $docComment->setIntent($intent);
            $lines[] = $docComment;
        }

        foreach ($others as $other) {
            // Add other lines with the given indentation.
            $lines[] = $intent . $other;
        }

        // Join lines with PHP_EOL and normalize the string.
        return static::normalizeString(implode(PHP_EOL, $lines));
    }

    /**
     * Get the current indentation.
     *
     * @return string The current indentation.
     */
    public function getIntent(): string
    {
        return $this->intent;
    }

    /**
     * Set the indentation.
     *
     * @param string $intent The indentation string.
     */
    public function setIntent(string $intent): void
    {
        $this->intent = $intent;
    }
}
