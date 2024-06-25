<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/05/2024
 */

namespace Autumn\Database\Models;

use Autumn\Lang\StringBuilder;

class Conditions extends StringBuilder
{
    /**
     * Create a Conditions instance from an iterable.
     *
     * @param iterable $data The input iterable.
     * @param string|\Closure|null $joiner The joiner string or a closure that returns a joiner string.
     * @return static The created Conditions instance.
     */
    public static function of(iterable $data, string|\Closure $joiner = null): static
    {
        if (!$joiner instanceof \Closure) {
            $joiner ??= 'AND';
            $joiner = fn() => $joiner === 'AND' ? ' AND ' : ' OR ';
        }

        return parent::of($data, $joiner);
    }
}
