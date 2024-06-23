<?php

namespace Autumn\Database\Traits;

use Autumn\Attributes\Transient;
use Autumn\Database\Db;
use Autumn\Database\DbConnection;
use Autumn\Database\DbException;
use Autumn\Database\Interfaces\Creatable;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\Persistable;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Interfaces\Updatable;
use Autumn\Exceptions\ForbiddenException;
use DateTimeImmutable;

trait EntityManagerTrait
{
    use RepositoryTrait;

    #[Transient]
    private bool $ignoreOnCreate = false;

    /**
     * @return bool
     */
    public function isIgnoreOnCreate(): bool
    {
        return $this->ignoreOnCreate;
    }

    /**
     * @param bool $ignoreOnCreate
     */
    public function setIgnoreOnCreate(bool $ignoreOnCreate): void
    {
        $this->ignoreOnCreate = $ignoreOnCreate;
    }

    /**
     * Find the record and return the instance
     *
     * @param array $data
     * @return static
     */
    public static function findFrom(array $data): static
    {
        $instance = static::readonly()->fromArray($data);
        foreach ($data as $name => $value) {
            if ($column = static::entity_column($name)?->getName()) {
                $instance->and($column, $instance->__get($name));
            }
        }
        return $instance;
    }

    /**
     * Create a record and return the instance
     *
     * @param array $data
     * @param bool|null $ignore
     * @return EntityManagerTrait|null
     */
    public static function createFrom(array $data, bool $ignore = null): ?static
    {
        if ($manager = Db::manager(static::from($data))) {
            $manager->persist();
            if ($manager instanceof static) {
                return $manager;
            }
        }

        if ($connection = Db::connection(static::entity_class())) {
            $data = static::from($data)->withConnection(Db::of($connection));
            $data->setIgnoreOnCreate($ignore ?? false);
            if ($data->persist()) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Persists the current entity object.
     *
     * This method can create or update the entity object based on its state (new or existing).
     * It triggers appropriate events and performs data validation before and after the operation.
     *
     * @param array|null $changes Optional. An array of changes to apply. If provided, it updates the object's data.
     *
     * @throws DbException If a database operation fails.
     * @throws ForbiddenException If the instance is read-only or does not have a valid database connection.
     *
     * @return bool Returns true if the persist operation is successful, otherwise false.
     *
     * @deprecated
     */
    public function __persist__(array $changes = null, DbConnection $db = null): bool
    {
        if (!$this instanceof Persistable || !$this instanceof EntityInterface) {
            return false;
        }

        if (!($db ??= $this->connection())) {
            throw ForbiddenException::of('The instance of `%s` is readonly.', static::class);
        }

        $originData = $this->toArray();
        if ($changes) {
            $this->fromArray($changes);
        }

        $action = $this->isNew() ? 'create' : 'update';
        if (!$this->fire($action, $this)) {
            return false;
        }

        $this->validate($action);

        if ($this instanceof Updatable) {
            if ($updatedAtColumn = static::column_updated_at()) {
                $this[$updatedAtColumn] = new DateTimeImmutable;
            }
        }

        if ($action === 'create') {
            if ($this instanceof Creatable) {
                if ($createdAtColumn = static::column_created_at()) {
                    $this[$createdAtColumn] = new DateTimeImmutable;
                }
            }
            $data = $this->toArray();
        } else {
            $data = [];

            foreach ($this->toArray() as $name => $value) {
                if ($value !== ($originData[$name] ?? null)) {
                    $data[$name] = $value;
                }
            }

            if ($this instanceof Creatable) {
                if ($createdAtColumn = static::column_created_at()) {
                    unset($data[$createdAtColumn]);
                }
            }
        }

        unset($data[$pk = static::column_primary_key()]);

        foreach ($data as $name => $value) {
            if (is_array($value)) {
                $data[$name] = json_encode($value);
            }
        }

        if ($action === 'create') {

            $id = $db->insert(static::entity_name(), $data, $this->ignoreOnCreate);
            if ($id > 0) {
                $this->setId($id);
                $result = true;
            }

            $action = 'created';
        } else {
            if ($data && $db->update(static::entity_name(), $data, [$pk => $this->getId()])) {
                $result = true;
            }
            $action = 'updated';
        }

        return ($result ?? null) && $this->fire($action, $this, $changes);
    }

    /**
     * Deletes the current entity object.
     *
     * This method triggers the `delete` event before deletion and the `deleted` event after deletion.
     * If the instance is read-only or does not have a valid database connection, it throws a `ForbiddenException`.
     *
     * @return bool Returns true if the deletion is successful, otherwise false.
     * @throws ForbiddenException If the instance is read-only or does not have a valid database connection.
     *
     * @throws DbException If a database operation fails.
     *
     * @deprecated
     */
    private function __destroy__(?DbConnection $db = null): bool
    {
        if (!$this instanceof Persistable || !$this instanceof EntityInterface) {
            return false;
        }

        if (!($id = $this->getId())) {
            return false;
        }

        if (!($db = $this->connection())) {
            throw ForbiddenException::of('The instance of `%s` is readonly.', static::class);
        }

        if (!$this->fire('delete', $this)) {
            return false;
        }

        $this->validate('delete');


        $result = $db->delete(static::entity_name(), [
            static::column_primary_key() => $id
        ]);

        if ($result) {
            $this->fire('deleted', $this);
        }

        return !!$result;
    }

    public function queryByRequest(array|\ArrayAccess $request, array $context = null, array &$args = null): RepositoryInterface
    {
        return $this->getList($context);
    }

}