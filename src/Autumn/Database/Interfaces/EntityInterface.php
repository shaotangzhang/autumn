<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/05/2024
 */

namespace Autumn\Database\Interfaces;

use Autumn\Interfaces\ModelInterface;

interface EntityInterface extends ModelInterface
{
    public static function entity_name(): string;
}