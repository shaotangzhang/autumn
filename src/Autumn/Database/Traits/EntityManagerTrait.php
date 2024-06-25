<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace Autumn\Database\Traits;

use App\Models\User\User;
use Autumn\Attributes\Transient;
use Autumn\Database\Db;
use Autumn\Database\DbConnection;
use Autumn\Database\DbException;
use Autumn\Database\Events\EntityCreatedEvent;
use Autumn\Database\Events\EntityCreatingEvent;
use Autumn\Database\Events\EntityEventDispatcher;
use Autumn\Database\Events\EntityEventHandlerInterface;
use Autumn\Database\Events\EntityEventInterface;
use Autumn\Database\Events\EntityUpdatedEvent;
use Autumn\Database\Events\EntityUpdatingEvent;
use Autumn\Database\Interfaces\Creatable;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\EntityManagerInterface;
use Autumn\Database\Interfaces\Persistable;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Interfaces\Updatable;
use Autumn\Database\Models\Repository;
use Autumn\Events\Event;
use Autumn\Exceptions\ForbiddenException;
use Autumn\Exceptions\NotFoundException;
use Autumn\Exceptions\ServerException;
use Autumn\Exceptions\SystemException;
use DateTimeImmutable;

trait EntityManagerTrait
{
    use EntityRepositoryTrait;

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
     * Hooks an Entity Event Handler to handle all the necessary events of this entity
     *
     * @param string|EntityEventHandlerInterface $handler
     * @return void
     */
    public static function hook(string|EntityEventHandlerInterface $handler): void
    {
        EntityEventDispatcher::hook(static::class, $handler);
    }

    public static function repository(array $context = null, DbConnection $connection = null): RepositoryInterface
    {
        return Repository::of(static::class, $context, $connection);
    }

    public static function find(array|int $context): ?static
    {
        return static::findBy($context)->query()->fetch();
    }

    public static function findBy(array|int $context): RepositoryInterface
    {
        if (is_int($context)) {
            $context = [Db::entity_primary_key(static::class) => $context];
        }

        $context['limit'] ??= 1;
        $context['page'] ??= 1;
        $context['limit_max'] ??= 1;
        $context['limit_default'] ??= 1;

        return static::repository($context);
    }

    public static function findOrFail(array|int $context, string $messageIfNotFound = null): static
    {
        return static::find($context) ?? throw NotFoundException::of($messageIfNotFound);
    }

    public static function findOrNew(array|int $context, array $extra = null): static
    {
        if ($instance = static::find($context)) {
            return $instance;
        }

        $data = array_merge($extra ?? [], is_array($context) ? $context : []);
        return static::createFrom($data);
    }

    public static function findOrCreate(array|int $context, array $extra = null): static
    {
        if ($instance = static::find($context)) {
            return $instance;
        }

        $data = array_merge($extra ?? [], is_array($context) ? $context : []);
        return static::create($data);
    }

    /**
     * Creates a new record in the database.
     *
     * @param array $entity The data to insert.
     * @return static|null The created entity.
     * @throws ServerException
     */
    public static function create(array $entity): ?static
    {
        $tableName = static::entity_name();

        // Create an instance and fill it with provided data
        $instance = static::from($entity);

        // Dispatch 'creating' event
        if (Event::dispatch(new EntityCreatingEvent($instance)) === false) {
            return null;
        }

        // Filter and prepare data for insertion
        $filteredData = static::filter_data_on_create($instance);

        // Insert the data into the database
        $db = Db::forEntity(static::class);
        $id = $db->insert($tableName, $filteredData);

        if ($id < 1) {
            throw ServerException::of('Failed to insert record of `%s`.', $tableName);
        }

        // Set the ID of the newly created instance
        $instance->setId($id);

        // Dispatch 'created' event
        Event::dispatch(new EntityCreatedEvent($instance));

        return $instance;
    }

    /**
     * Filters and prepares data for insertion into the database on create operation.
     *
     * @param self $instance The entity instance being created.
     * @return array The filtered data for insertion.
     */
    public static function filter_data_on_create(self $instance): array
    {
        $avoidColumns = [];

        foreach (static::entity_guarded_columns() as $column) {
            if ($column->isCurrentTimestampOnCreate()) {
                // Set the column value to the current timestamp if applicable
                $column->getProperty()->setValue($instance, new DateTimeImmutable());
            } elseif ($column->isPrimaryKey()) {
                // Reset the property value to its default if it's a primary key
                $instance->__property_reset__($column->getName());
                $avoidColumns[] = $column->getName();
            }
        }

        $data = $instance->toArray();

        // Remove columns that should be avoided
        foreach ($avoidColumns as $column) {
            unset($data[$column]);
        }

        return $data;
    }

    /**
     * Updates an existing record in the database.
     *
     * @param int|self $entity The entity ID or the entity instance to update.
     * @param array|null $changes The data to update.
     * @return false|EntityManagerTrait|null The updated entity, or null if update failed.
     * @throws DbException
     * @throws ServerException If the database update operation fails.
     */
    public static function update(int|self $entity, array $changes = null): false|static|null
    {
        $tableName = static::entity_name();

        // Load the existing entity from the database or use the provided instance
        if (is_int($entity)) {
            if (empty($changes)) {
                return false; // Handle case where nothing to change
            }

            // Load the existing entity from the database
            $instance = static::find($entity);
            if (!$instance) {
                return null; // Handle case where entity with given ID does not exist
            }
        } else {
            $instance = clone $entity;
        }

        // Track original data for comparison if changes are provided
        $originData = $instance->toArray();
        if ($changes) {
            $instance->fromArray($changes);
        }


        // Create the event instance
        $updatingEvent = new EntityUpdatingEvent($instance, $changes);

        // Dispatch the 'updating' event
        if (Event::dispatch($updatingEvent) === false) {
            return null;    // Handle case where event listener stops the update
        }

        // Filter and prepare data for update
        $filteredData = static::filter_data_on_update($instance);

        // Remove unchanged fields from the update data
        foreach ($filteredData as $name => $value) {
            if ($value === ($originData[$name] ?? null)) {
                unset($filteredData[$name]);
            }
        }

        if (empty($filteredData)) {
            return $instance; // No changes to update
        }

        // Perform the database update
        $affectedRows = Db::forEntity(static::class)->update($tableName, $filteredData, [
            static::column_primary_key() => $instance->getId()
        ]);

        if ($affectedRows < 1) {
            throw new ServerException(sprintf("Failed to update record of `%s` with ID %d.", $tableName, $instance->getId()));
        }

        // Dispatch the 'updated' event
        Event::dispatch(new EntityUpdatedEvent($instance));

        return $instance;
    }

    /**
     * Filters and prepares data for update operation in the database.
     *
     * @param self $instance The entity instance being updated.
     * @return array The filtered data for update.
     */
    public static function filter_data_on_update(self $instance): array
    {
        $filteredData = [];

        foreach (static::entity_fillable_columns() as $column) {
            $filteredData[$column->getName()] = $instance->__property_get__($column->getName());
        }

        foreach (static::entity_guarded_columns() as $column) {
            if ($column->isCurrentTimestampOnUpdate()) {
                $filteredData[$column->getName()] = new DateTimeImmutable();
            } else {
                unset($filteredData[$column->getName()]);
            }
        }

        return $filteredData;
    }

    public static function delete(EntityManagerInterface|int $entity): static
    {

    }

    public function save(array $changes = null): bool
    {
    }

    public function destroy(): bool
    {

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
     * @throws \Throwable
     */
    public static function createFrom(array $data, bool $ignore = null): ?static
    {
        if ($connection = Db::forEntity(static::class)) {
            $connection->transactional(function ($connection) use ($data, $ignore) {

                $instance = static::from($data);


                if ($column = Db::entity_updated_at(static::class)) {
                    Db::entity_property_set($instance, $column, new DateTimeImmutable);
                }

                $id = $connection->insert(Db::entity_name(static::class), $data, $ignore);
                if ($id) {
                    return static::find([Db::entity_primary_key(static::class) => $id]);
                }


            });
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