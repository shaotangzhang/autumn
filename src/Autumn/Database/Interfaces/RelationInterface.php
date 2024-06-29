<?php
/**
 * Autumn PHP Framework
 *
 * Date:        14/05/2024
 */

namespace Autumn\Database\Interfaces;

interface RelationInterface extends Extendable
{
    public static function relation_secondary_class(): ?string;
}