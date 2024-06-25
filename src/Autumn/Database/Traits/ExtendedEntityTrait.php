<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Traits;

trait ExtendedEntityTrait
{
    use PrimaryIdColumnTrait;
    public static function relation_primary_class(): string
    {
        return static::RELATION_PRIMARY_CLASS ?? '';
    }

    public function getPrimary(): mixed
    {
        return $this->hasOne(static::relation_primary_class(), static::relation_primary_column());
    }

    public function setPrimary(array|object $value = null): void
    {
        $this->hasOneSet(static::relation_primary_class(), static::relation_primary_column(), $value);
    }
}