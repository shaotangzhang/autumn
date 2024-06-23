<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Database\Interfaces;

interface RelationInterface extends ExtendedEntityInterface
{
    public static function relation_secondary_class(): string;

    public static function relation_secondary_column(): string;

    public function getSecondaryId(): int;
}