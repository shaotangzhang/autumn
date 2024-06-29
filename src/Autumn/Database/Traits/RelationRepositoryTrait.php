<?php

namespace Autumn\Database\Traits;

use Autumn\Database\DbConnection;
use Autumn\Database\Interfaces\RepositoryInterface;

/**
 * Trait RelationRepositoryTrait
 *
 * @deprecated Not complete yet.
 *
 * @package     Autumn\Database\Traits
 * @since       29/06/2024
 */
trait RelationRepositoryTrait
{
    use RepositoryTrait;
    use EntityRelationshipTrait;

    public function __construct(array $context = null, DbConnection $connection = null)
    {
        $this->connection = $connection;
        $this->modelClass = static::class;

        if ($context) {
            $this->__prepare_from_context__($context);
        }
    }

    public static function of(array $context = null, DbConnection $connection = null): static|RepositoryInterface
    {
        return new static($context, $connection);
    }

    public static function none(array $context = null, DbConnection $connection = null): RepositoryInterface
    {
        return static::of($context, $connection)->where('false');
    }
}