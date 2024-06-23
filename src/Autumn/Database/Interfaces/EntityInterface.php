<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Database\Interfaces;

interface EntityInterface
{
    public static function entity_name(): string;

    public static function entity_primary_key(): string;
}