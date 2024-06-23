<?php
/**
 * Autumn PHP Framework
 *
 * Date:        30/01/2024
 */

namespace Autumn\Lang;

use Autumn\Http\HTMLDocument;
use Autumn\Interfaces\Renderable;
use Closure;

class HTML
{
    public const ENCLOSED_TAGS = ['img', 'hr', 'br', 'meta', 'link', 'base', 'col', 'area', 'wbr', 'input'];

    /**
     * Encode HTML entities in a string.
     *
     * @param string|null $text The input string to encode.
     * @return string The encoded string.
     */
    public static function encode(?string $text): string
    {
        return $text ? htmlspecialchars($text, ENT_QUOTES, 'UTF-8') : '';
    }

    /**
     * Decode HTML entities in a string.
     *
     * @param string|null $html The input string to decode.
     * @return string The decoded string.
     */
    public static function decode(?string $html): string
    {
        return $html ? htmlspecialchars_decode($html) : '';
    }

    public static function escapeAttribute(?string $value): string
    {
        return isset($value) ? htmlspecialchars($value) : '';
        // return str_replace('"', '&quot;', $value);
    }

    public static function renderAttribute(string $name, string|bool|array|null $value, string $prefix = null): string
    {
        if ($value === null || $value === false) {
            return '';
        }

        if ($value === true) {
            return ' ' . $prefix . $name;
        }

        if (is_array($value)) {
            if (!array_is_list($value)) {
                return static::renderAttributes($value, $prefix . $name . '-');
            }

            $value = implode(' ', $value);
        }

        $name = htmlspecialchars($name);
        $escaped = static::escapeAttribute($value);
        return " $name=\"$escaped\"";
    }

    public static function renderAttributes(array $attributes, string $prefix = null): string
    {
        $list = [];
        foreach ($attributes as $name => $value) {

            switch ($name) {
                case 'class':
                    if (empty($prefix)) {
                        $list[] = static::renderAttribute($name, static::renderClassAttribute($value));
                    }
                    continue 2;

                case 'style':
                    if (empty($prefix)) {
                        $list[] = static::renderAttribute($name, static::renderStyleAttribute($value));
                    }
                    continue 2;

                default:
                    if (is_array($value)) {
                        if (!is_string($name)) {
                            $code = static::renderAttribute($name, implode(' ', $value), $prefix);
                        } else {
                            $code = static::renderAttributes($value, $prefix . $name . '-');
                        }
                    } else {
                        $code = static::renderAttribute($name, $value, $prefix);
                    }

                    if (!empty($code)) {
                        $list[] = $code;
                    }
            }
        }

        return implode('', $list);
    }

    public static function renderClassList(string|array|\Closure|null ...$classes): string
    {
        return static::renderClassAttribute($classes);
    }

    public static function inClassList(mixed $classList, string ...$classes): ?string
    {
        $classList = [];
        static::parseClassAttribute($classes, null, $classList);

        foreach ($classes as $class) {
            if ($classList[$class] ?? null) {
                return $class;
            }
        }

        return null;
    }

    public static function renderClassAttribute(mixed $classes, string $prefix = null): string
    {
        $classList = [];
        static::parseClassAttribute($classes, $prefix, $classList);
        return implode(' ', array_keys(array_filter(
            $classList, fn($value, $key) => !empty($value) && !is_int($key), ARRAY_FILTER_USE_BOTH
        )));
    }

    public static function parseAttributes(string $str): array
    {
        $attributes = [];

        $str = strtr($str, ['<' => '&lt;', '>' => '&gt;']);

        static $html;
        $html ??= new HTMLDocument;

        if ($dom = HTMLDocument::fromHTML("<div $str></div>")) {
            foreach ($dom->getElementsByTagName('div') as $element) {
                foreach ($element->attributes as $attribute) {
                    $attributeName = $attribute->name;
                    if ($attributeValue = $attribute->value) {
                        $attributeValue = htmlspecialchars_decode($attributeValue);
                    }
                    $attributes[$attributeName] = $attributeValue;
                }
            }
        }

        return $attributes;
    }


    public static function parseClassAttribute(mixed $classes, string $prefix = null, array &$classList = []): void
    {
        while ($classes instanceof Closure) {
            $classes = $classes($prefix);
        }

        if (is_iterable($classes)) {
            foreach ($classes as $name => $value) {
                if (is_int($name)) {
                    static::parseClassAttribute($value, $prefix, $classList);
                } else {
                    while ($value instanceof Closure) {
                        $value = call_user_func($value, $prefix);
                    }

                    $classList[$name] = !!$value;
                }
            }
        } elseif (is_string($classes)) {
            foreach (preg_split('/\s+/', $classes) as $class) {
                $classList[$prefix . $class] = true;
            }
        } elseif ($classes) {
            static::parseClassAttribute($classes, $prefix, $classList);
        }
    }

    public static function renderStyleAttribute(mixed $styles, string $prefix = null): string
    {
        $styleList = [];

        if (is_array($styles)) {
            $styles = array_filter(array_map(function ($name, $item) use ($prefix) {
                while ($item instanceof \Closure) {
                    $item = call_user_func($item, $prefix . $name);
                }

                return !empty($item) ? $name . ': ' . (is_array($item) ? implode('', $item) : $item) : null;
            }, array_keys($styles), $styles));

            if (!empty($styles)) {
                $styleList[] = implode('; ', $styles);
            }
        } elseif (!empty($styles)) {
            if ($prefix) {
                $styleList[] = "$prefix: $styles";
            } else {
                $styleList[] = $styles;
            }
        }

        return implode('; ', $styleList);
    }

    public static function renderElement(string $tagName,
                                         array  $attributes = null,
                                         array  $context = null,
                                         array  $children = null,
                                         string $textContent = null,
                                         bool   $enclosure = null): string
    {
        ob_start();

        try {
            static::outputElement($tagName, $attributes, $context, $children, $textContent, $enclosure);
            return ob_get_contents();
        } catch (\Throwable $ex) {
            if (is_callable($callback = $context['fallback'] ?? null)) {
                call_user_func($callback, $ex, func_get_args());
            }
            return '';
        } finally {
            ob_end_clean();
        }
    }

    public static function outputElement(string   $tagName,
                                         array    $attributes = null,
                                         array    $context = null,
                                         iterable $children = null,
                                         string   $textContent = null,
                                         bool     $enclosure = null,
    ): void
    {
        $tagName = $context['tagName'] ?? $tagName;
        $textContent ??= $context['textContent'] ?? null;
        $enclosure ??= $context['enclosure'] ?? in_array(strtolower($tagName), static::ENCLOSED_TAGS);

        if ($tagName) {
            echo '<', $tagName;
            if ($attributes) {
                echo static::renderAttributes($attributes);
            }
        }

        if ($enclosure) {
            echo '/>';
        } else {
            if ($tagName) {
                echo '>';
            }

            $callback = $context['renderCallback'] ?? null;
            if ($callback instanceof Closure) {
                call_user_func($callback, $context, $textContent, $children);
            } else {
                static::output(static::encode($textContent));
                static::output($children);
            }

            if ($tagName) {
                echo '</', $tagName, '>';
            }
        }
    }

    public static function renderTag(string $tagName, array $attributes = null, array $context = null, string|iterable|Renderable|Closure ...$children): string
    {
        return static::renderElement($tagName, $attributes, $context, $children);
    }

    public static function output(mixed $content, mixed ...$args): void
    {
        while ($content instanceof \Closure) {
            $content = call_user_func_array($content, $args);
        }

        if (is_iterable($content)) {
            foreach ($content as $item) {
                static::output($item);
            }
            return;
        }

        if ($content instanceof Renderable) {
            $content->render();
            return;
        }

        if ($content instanceof \DateTimeInterface) {
            $content = static::renderDatetime($content);
        }

        echo $content;
    }

    public static function renderDatetime(\DateTimeInterface $content): string
    {
        return $content->format('c');
    }

    public static function element(string $tagName, array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::renderElement($tagName ?: 'div', $attributes, $context, $children);
    }

    public static function div(array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element($context['tagName'] ?? 'div', $attributes, $context, $children);
    }

    public static function span(string|iterable|Renderable|\Closure $text = null, array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element($context['tagName'] ??= 'span', $attributes, $context, $text, $children);
    }

    public static function input(array $attributes = null, array $context = null, mixed ...$children): string
    {
        unset($context['enclosure']);
        return static::element($context['tagName'] ??= 'input', $attributes, $context, ...$children);
    }

    public static function hiddenInputs(iterable $variables): string
    {
        $lines = [];
        foreach ($variables as $name => $value) {
            $lines[] = static::element('input', ['type' => 'hidden', 'name' => $name, 'value' => $value]);
        }
        return implode(PHP_EOL, $lines);
    }

    public static function button(string $text, array $attributes = null, array $context = null, mixed ...$children): string
    {
        if ($context['textNoWrap'] ?? null) {
            $text = '<span class="text-nowrap">' . $text . '</span>';
        }
        return static::element('button', $attributes, $context, $text, ...$children);
    }

    public static function a(string $href, string|iterable|Renderable|\Closure $text = null, array $attributes = null, array $context = null, mixed ...$children): string
    {
        $attributes['href'] = $href;
        return static::element('a', $attributes, $context, $text, ...$children);
    }

    public static function img(string $src, array $attributes = null, array $context = null): string
    {
        $attributes['src'] = $src;
        $context['enclosure'] = true;
        return static::element('img', $attributes, $context);
    }

    public static function p(array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element('p', $attributes, $context, ...$children);
    }

    public static function h1(string $text, array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element('h1', $attributes, $context, $text, ...$children);
    }

    public static function ul(array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element('ul', $attributes, $context, $children);
    }

    public static function li(array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element('li', $attributes, $context, $children);
    }

    public static function table(array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element('table', $attributes, $context, $children);
    }

    public static function tr(array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element('tr', $attributes, $context, $children);
    }

    public static function td(array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element('td', $attributes, $context, $children);
    }

    public static function form(array $attributes = null, array $context = null, mixed ...$children): string
    {
        return static::element('form', $attributes, $context, $children);
    }

    public static function option(mixed $value = null, string|iterable|Renderable|Closure $text = null, array $attributes = null, array $context = null, mixed ...$children): string
    {
        $attributes['value'] = $value;
        $attributes['selected'] ??= isset($context['inputValue']) ? ($value === $context['inputValue']) : null;
        return static::element('option', $attributes, $context, $text, $children);
    }

    public static function options(iterable $options, string $label = null, array $attributes = null, array $context = null): string
    {
        $list = [];
        $inputValue = $context['inputValue'] ?? null;
        if (isset($inputValue)) {
            $inputValue = (string)$inputValue;
        }

        $textKey = $context['html_option_text_key'] ?? 'text';
        $valueKey = $context['html_option_value_key'] ?? 'value';

        foreach ($options as $value => $text) {
            if (is_int($value)) {
                if (is_array($text) && isset($text[$valueKey])) {
                    $value = (string)$text[$valueKey];
                    $text = $text[$textKey] ?? $value;
                    $selected = ['selected' => $inputValue === $value];
                } else {
                    $text = (string)$text;
                    $selected = ['selected' => $inputValue === $text];
                }
            } else {
                $selected = ['selected' => $inputValue === $value];
            }

            $list[] = static::option(is_int($value) ? null : $value, $text, $selected + ($attributes ?? []), $context);
        }

        $list = implode(PHP_EOL, $list);
        if ($label === null) {
            return $list;
        }

        return static::element('optgroup', ['label' => $label], $context, $list);
    }

    public static function select(iterable $options = null, mixed $inputValue = null, array $attributes = null, array $context = null, iterable $optionGroups = null): string
    {
        $context['inputValue'] = $inputValue;
        $id = $attributes['id'] ?? null;

        $placeholder = $context['placeholder'] ?? $attributes['placeholder'] ?? null;
        $context['renderCallback'] = function () use ($context, $optionGroups, $options, $placeholder) {

            if (is_iterable($placeholder)) {
                echo static::options($placeholder);
            } elseif ($placeholder !== null) {
                echo static::option($context['placeholderValue'] ?? $context['defaultValue'] ?? '', $placeholder, [
                    'selected' => $context['placeholderSelected'] ?? null,
                    'disabled' => $context['placeholderDisabled'] ?? null,
                ]);
            }

            echo static::options($options, null, null, $context);
            foreach ($optionGroups ?? [] as $label => $group) {
                echo static::options($group, $label, null, $context);
            }
        };

        if ($id) {
            $attributes['id'] = $id;
        }
        return static::element('select', $attributes, $context);
    }

    public static function datalist(iterable $options = null, array $attributes = null, array $context = null): string
    {
        return static::element('datalist', $attributes, [
                'renderCallback' => function () use ($options) {
                    foreach ($options ?? [] as $key => $value) {
                        if (is_int($key)) {
                            echo static::option(null, $value);
                        } else {
                            echo static::option($key, $value);
                        }
                    }
                }
            ] + ($context ?? []));
    }

    public static function label(mixed $label, string $for = null, array $attributes = null, array $context = null): string
    {
        if ($for) {
            $attributes['for'] = $for;
        }

        return static::element('label', $attributes, $context, $label);
    }

    public static function icon(string $icon, array $attributes = null, array $context = null, mixed ...$children): string
    {
        $attributes['class'] = static::mergeToAttributeClass($attributes, [$icon], $context['extClass'] ?? null);
        return HTML::element($context['tagName'] ?? 'i', $attributes, $context, ...$children);
    }

    public static function mergeToAttributeClass(array &$attributes = null, mixed $classes = [], mixed $extClass = null): array
    {
        return $attributes['class'] = [$attributes['class'] ?? null, $classes, $extClass];
    }

    public static function stripTags(?string $html, string|array $allowedTags = null): string
    {
        if (!$html || !($html = trim($html))) {
            return '';
        }
        
        // Remove script and style tags and their content
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

        // Remove all HTML tags and decode HTML entities
        $text = strip_tags($html, $allowedTags);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($text);
    }
}