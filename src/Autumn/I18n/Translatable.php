<?php
/**
 * Autumn PHP Framework
 *
 * Date:        14/06/2024
 */

namespace Autumn\I18n;

interface Translatable
{
    public function translate(string $text, mixed ...$args): ?string;
}