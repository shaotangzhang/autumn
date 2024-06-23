<?php
/**
 * Autumn PHP Framework
 *
 * Date:        9/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\DbException;

trait RecyclableEntityManagerTrait // extends RecyclableEntity implements RecyclableEntityManagerInterface
{
    use EntityManagerTrait;
    use RecyclableRepositoryTrait;

    /**
     * @throws DbException
     */
    public function softDelete(): bool
    {
        if (!$this->isTrashed()) {
            if ($id = $this->getId()) {
                if ($column = static::column_deleted_at()) {
                    if ($db = static::on()?->connection()) {
                        if ($this->fire('trash', $this)) {
                            $result = $db->update(static::entity_name(), [
                                $column => $time = new \DateTimeImmutable,
                            ], [
                                "($column IS NULL OR $column > CURRENT_TIMESTAMP())",
                                static::column_primary_key() => $id
                            ]);

                            if ($result) {
                                $this->$column = $time;
                                $this->fire('trashed', $this);
                            }
                        }
                    }
                }
            }
        }

        return $result ?? false;
    }

    /**
     * @throws DbException
     */
    public function resetDelete(): bool
    {
        if ($this->isTrashed()) {
            if ($id = $this->getId()) {
                if ($column = static::column_deleted_at()) {

                    if ($db = $this->connection()) {
                        if ($this->fire('restore', $this)) {
                            $result = $db->update(static::entity_name(), [
                                $column => null,
                            ], [
                                "($column IS NOT NULL AND $column <= CURRENT_TIMESTAMP())",
                                static::column_primary_key() => $id
                            ]);

                            if ($result) {
                                $this->$column = null;
                                $this->fire('restored', $this);
                            }
                        }
                    }
                }
            }
        }

        return $result ?? false;
    }
}