<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/05/2024
 */

namespace Autumn\System\Services;

use Autumn\Database\Models\Relation;

interface MultipleTypesRelationServiceInterface
{
    public static function forType(string $type): self;

    public static function forClass(string $class): self;

    public static function forRelation(string|Relation $relationClass, string $alias, string $primaryAlias = null): self;
}