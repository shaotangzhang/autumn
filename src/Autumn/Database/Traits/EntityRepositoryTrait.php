<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\DbConnection;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Models\Repository;
use Autumn\Exceptions\NotFoundException;

trait EntityRepositoryTrait
{
    use RepositoryTrait;
    use RelatedRepositoryTrait;

    public static function repository(array $context = null, DbConnection $connection = null): RepositoryInterface
    {
        return Repository::of(static::class, $context, $connection);
    }

    public function first(): ?static
    {
        return ($this->resultSet ?? $this->query())->fetch();
    }

    public function firstOrFail(string $error = null): static
    {
        return $this->first() ?? throw NotFoundException::of($error);
    }

}