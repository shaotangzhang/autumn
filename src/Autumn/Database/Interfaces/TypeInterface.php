<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Interfaces;

interface TypeInterface
{
    public static function defaultType(): string;

    public function getType(): string;
}