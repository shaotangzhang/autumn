<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Database\Interfaces;

interface ExtendedEntityInterface extends EntityInterface
{
    public static function relation_primary_class(): string;

    public static function relation_primary_column(): string;
}