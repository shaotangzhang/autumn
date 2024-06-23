<?php
/**
 * Autumn PHP Framework
 *
 * Date:        14/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Models\Entity;
use Autumn\Exceptions\ValidationException;

trait ExtendedEntityManagerTrait
{
    use EntityManagerTrait;
    use ExtendedEntityTrait;

    public static function entriesOf(int|Entity $entity): static
    {
        $primaryClass = static::relation_primary_class();
        if (is_object($entity)) {
            if (!is_subclass_of($entity, $primaryClass, true)) {
                throw ValidationException::of('The argument must be the ID or the instance of entity %s.', $primaryClass);
            }
        }

        return static::readonly()
            ->where(static::relation_primary_column(), is_int($entity) ? $entity : $entity->getId());
    }
}