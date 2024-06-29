<?php

namespace Autumn\Database\Traits;

use Autumn\Database\Db;
use Autumn\Database\DbException;
use Autumn\Database\Events\EntityCreatedEvent;
use Autumn\Database\Events\EntityCreatingEvent;
use Autumn\Database\Events\EntityDeletedEvent;
use Autumn\Database\Events\EntityDeletingEvent;
use Autumn\Database\Events\EntityUpdatedEvent;
use Autumn\Database\Events\EntityUpdatingEvent;
use Autumn\Database\Models\Entity;
use Autumn\Events\Event;
use Autumn\Exceptions\NotFoundException;
use Autumn\Exceptions\ServerException;
use Autumn\Exceptions\SystemException;
use DateTimeImmutable;

trait EntityManagerTrait
{
    use EntityRepositoryTrait;

    /**
     * Find an entity by the given context.
     *
     * @param array|int $context The search context or the primary key value.
     * @return static|null The found entity or null if not found.
     */
    public static function find(array|int $context): ?static
    {
        return static::findBy($context)->query()->fetch();
    }

    /**
     * Find an entity by the given context and return the repository instance.
     *
     * @param array|int $context The search context or the primary key value.
     * @return static The repository instance with the search conditions applied.
     */
    public static function findBy(array|int $context): static
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

    /**
     * Find an entity by the given context or throw a NotFoundException if not found.
     *
     * @param array|int $context The search context or the primary key value.
     * @param string|null $messageIfNotFound The exception message if the entity is not found.
     * @return static The found entity.
     * @throws NotFoundException If the entity is not found.
     */
    public static function findOrFail(array|int $context, string $messageIfNotFound = null): static
    {
        return static::find($context) ?? throw NotFoundException::of($messageIfNotFound);
    }

    /**
     * Find an entity by the given context or return a new instance with the provided data.
     *
     * @param array|int $context The search context or the primary key value.
     * @param array|null $extra Additional data to initialize the new instance.
     * @return static The found entity or a new instance if not found.
     */
    public static function findOrNew(array|int $context, array $extra = null): static
    {
        if (is_int($context)) {
            $context = [Db::entity_primary_key(static::class) => $context];
        }

        if ($instance = static::find($context)) {
            return $instance;
        }

        $data = array_merge($extra ?? [], $context);
        return static::from($data);
    }

    /**
     * Find an entity by the given context or create a new one with the provided data.
     *
     * @param array|int $context The search context or the primary key value.
     * @param array|null $extra Additional data to initialize the new instance.
     * @return static The found entity or the newly created entity if not found.
     * @throws ServerException
     */
    public static function findOrCreate(array|int $context, array $extra = null): static
    {
        if (is_int($context)) {
            $context = [Db::entity_primary_key(static::class) => $context];
        }

        if ($instance = static::find($context)) {
            return $instance;
        }

        $data = array_merge($extra ?? [], $context);
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

        if (!$instance instanceof Entity) {
            throw SystemException::of('The instance `%s` is not an Entity.', $instance::class);
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

        if (!empty($filteredData)) {
            // Perform the database update
            $affectedRows = Db::forEntity(static::class)->update($tableName, $filteredData, [
                static::column_primary_key() => $instance->getId()
            ]);

            if ($affectedRows < 1) {
                throw new ServerException(sprintf("Failed to update record of `%s` with ID %d.", $tableName, $instance->getId()));
            }

            // Dispatch the 'updated' event
            Event::dispatch(new EntityUpdatedEvent($instance));
        }

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

    /**
     * Delete a model entity by ID or object instance.
     *
     * @param int|self $entity The ID of the entity or the entity object itself.
     * @return bool True if deletion is successful, false otherwise.
     * @throws DbException If entity cannot be found or deletion fails.
     */
    public static function delete(int|self $entity): bool
    {
        if (is_int($entity)) {
            // If $entity is an integer, assume it's the ID of the entity
            $entity = self::find($entity);
        }

        if (!$entity instanceof Entity) {
            throw new \InvalidArgumentException('Invalid entity provided.');
        }

        // Dispatch EntityDeletingEvent and check if propagation is stopped
        if (Event::dispatch(new EntityDeletingEvent($entity)) === false) {
            return false;
        }

        // Perform database deletion
        $affectedRows = Db::forEntity(static::class)->delete(static::entity_name(), [
            static::column_primary_key() => $entity->getId()
        ]);

        if ($affectedRows > 0) {
            // Dispatch EntityDeletedEvent after successful deletion
            Event::dispatch(new EntityDeletedEvent($entity));
            return true;
        }

        return false;
    }
}