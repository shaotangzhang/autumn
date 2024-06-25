<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Interfaces;

interface Extendable
{
    public static function relation_primary_class(): string;

    public static function relation_primary_column(): string;

    public function getPrimaryId(): int;
}