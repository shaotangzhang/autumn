<?php
/**
 * Autumn PHP Framework
 *
 * Date:        28/02/2024
 */

namespace Autumn\Lang;

class Boolean
{
    public static function bool(mixed $value): ?bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    }
}