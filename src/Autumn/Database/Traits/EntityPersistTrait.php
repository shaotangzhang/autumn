<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/06/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\DbConnection;
use Autumn\Database\DbException;
use Autumn\Events\Event;
use Autumn\Exceptions\ForbiddenException;
use Autumn\Exceptions\ValidationException;
use Autumn\System\Reflection;
use DateTimeImmutable;

trait EntityPersistTrait
{
    protected function __validate__(string $action, array $data = null, array &$validated = null): void
    {
        foreach (Reflection::fields(static::class) as $fieldName => $field) {

            if ($data && !array_key_exists($fieldName, $data)) {
                continue;
            }

            foreach (Column::ofReflection($field) as $column) {
                $name = $column->getName();
                $value = $this->{$field->getName()};

                if ($column->isNotNull()) {
                    if (is_null($value)) {
                        if (!$column->hasDefaultValue()) {
                            throw ValidationException::of('The field `%s` is required.', $fieldName);
                        }
                    }
                }

                if ($size = $column->getSize()) {
                    if ($column->isString()) {
                        if (strlen($value) > $size) {
                            throw ValidationException::of('The value of field `%s` must not be longer than %s characters.', $fieldName, $size);
                        }
                    }
                }

                if ($column->isUnsigned()) {
                    if ($column->isInt() || $column->isFloat()) {
                        if ($value < 0) {
                            throw ValidationException::of('The value of field `%s` must not be negative.', $fieldName);
                        }
                    }
                }

                $value = $value ?? $data[$name] ?? $column->getDefault();
                if (is_array($value) || ($value instanceof \JsonSerializable)) {
                    $value = json_encode($value);
                } elseif (is_object($value)) {
                    if ($value instanceof DateTimeImmutable) {
                        $value = serialize($value);
                    }
                }

                $validated[$name] = $value;
            }
        }
    }

    /**
     * @throws DbException
     */
    public function create(array $changes, DbConnection $db): bool
    {
        $originData = $this->toArray();
        $this->fromArray($changes);

        $this->validate('create');
        Event::fire('create', $this, $changes);

        if ($updatedAtColumn = static::column_updated_at()) {
            $this[$updatedAtColumn] = new DateTimeImmutable;
        }

        if ($createdAtColumn = static::column_created_at()) {
            $this[$createdAtColumn] = new DateTimeImmutable;
        }

        $this->__validate__('create', null, $data);
        unset($data[static::column_primary_key()]);

        try {
            $id = $db->insert(static::entity_name(), $data, static::IGNORE_DUPLICATE_ON_CREATE);
            if ($id < 0) {
                return false;
            }

            $this->setId($id);
            Event::fire('created', $this, $changes);
            return true;
        } catch (DbException $ex) {
            $this->fromArray($originData);
            throw $ex;
        }
    }

    /**
     * @throws DbException
     */
    public function update(array $changes, DbConnection $db): bool
    {
        $originData = $this->toArray();
        if ($changes) {
            $this->fromArray($changes);
        }

        $this->validate('update');
        Event::fire('update', $this, $changes);

        if ($updatedAtColumn = static::column_updated_at()) {
            $this[$updatedAtColumn] = new DateTimeImmutable;
        }

        try {
            if ($changes) {
                $changes = array_diff($this->toArray(), $originData);
            } else {
                $changes = $this->toArray();
            }

            $this->__validate__('update', $changes, $data);
            unset($data[$pk = static::column_primary_key()]);
            if ($createdAtColumn = static::column_created_at()) {
                unset($data[$createdAtColumn]);
            }

            $result = $data && $db->update(static::entity_name(), $data, [
                    $pk => $this->getId()
                ]);

            if ($result) {
                Event::fire('updated', $this, $data);
            }

            return $result;
        } catch (DbException $ex) {
            $this->fromArray($originData);
            throw $ex;
        }
    }

    /**
     * @throws DbException
     */
    public function persist(array $changes = null, DbConnection $db = null): bool
    {
        if (!$db) {
            throw ForbiddenException::of('The instance of `%s` is readonly.', static::class);
        }

        return $this->isNew()
            ? $this->create($changes ?? [], $db)
            : $this->update($changes ?? [], $db);
    }

    /**
     * @throws DbException
     */
    public function destroy(DbConnection $db = null): bool
    {
        if (!$db) {
            throw ForbiddenException::of('The instance of `%s` is readonly.', static::class);
        }

        $id = $this->getId();
        if ($id < 1) {
            throw ForbiddenException::of('The instance of `%s` is not persisted yet.', static::class);
        }

        $this->validate('delete');
        Event::fire('delete', $this, $id);

        if ($db->delete(static::entity_name(), [static::column_primary_key() => $this->getId()])) {
            Event::fire('deleted', $this, $id);
            return true;
        }

        return false;
    }

    public function validate(string $scenario = null): void
    {
        if ($scenario === 'delete') {
            throw ForbiddenException::of(
                'Unable to perform the `%s` action on this instance of `%s`.', $scenario,
                static::class
            );
        }
    }
}