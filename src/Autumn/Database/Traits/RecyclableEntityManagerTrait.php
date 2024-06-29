<?php
namespace Autumn\Database\Traits;

use Autumn\Database\DbException;
use Autumn\Exceptions\ServerException;

trait RecyclableEntityManagerTrait
{
    use EntityManagerTrait;
    use RecyclableEntityRepositoryTrait;

    /**
     * @throws ServerException
     * @throws DbException
     */
    public function trash(): bool
    {
        return !$this->isTrashed() && static::update($this, [
                static::column_deleted_at() => new \DateTimeImmutable
            ]);
    }

    /**
     * @throws ServerException
     * @throws DbException
     */
    public function restore(): bool
    {
        return $this->isTrashed() && static::update($this, [
                static::column_deleted_at() => null
            ]);
    }
}