<?php
/**
 * Autumn PHP Framework
 *
 * Date:        10/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Db;
use Autumn\Database\DbException;

trait RelationManagerTrait
{
    use RelationTrait;
    use EntityManagerTrait;

    /**
     * @param int $primaryId
     * @param int $secondaryId
     * @param array|null $context
     * @return RelationManagerTrait|null
     */
    public static function assign(int $primaryId, int $secondaryId, array $context = null): ?static
    {
        $context[static::relation_primary_column()] = $primaryId;
        $context[static::relation_secondary_column()] = $secondaryId;
        unset($context[static::column_primary_key()]);

        $context['DUPLICATE_KEY_ON_CREATE'] = 'ignore';

        $instance = static::find($key = [
            static::relation_primary_column() => $primaryId,
            static::relation_secondary_column() => $secondaryId
        ]);

        if (!$instance) {
            $context = array_merge($context ?? [], $key);
            $instance = static::createFrom($context, true);
        }

        return $instance;
    }

    /**
     * @throws DbException
     */
    public static function resign(int $primaryId = null, int $secondaryId = null): int
    {
        if ($primaryId > 0) {
            $context[static::relation_primary_column()] = $primaryId;
            if ($secondaryId > 0) {
                $context[static::relation_secondary_column()] = $secondaryId;
            }
            return Db::of()->delete(static::entity_name(), $context);
        } elseif ($secondaryId > 0) {
            return Db::of()->delete(static::entity_name(), $context[static::relation_secondary_column()] = $secondaryId);
        }

        return false;
    }
}
