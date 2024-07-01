<?php

namespace Autumn\System\Requests;

use Autumn\Database\Db;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Exceptions\SystemException;
use Autumn\Exceptions\ValidationException;

/**
 * Handles form request validation by applying rules to the input data.
 *
 * This class allows you to define validation rules, validate input data against
 * those rules, and collect validation results and errors. It also implements the
 * ArrayAccess interface for convenient access to request data.
 */
class FormRequest extends BaseRequest
{
    protected array $registeredClasses = [];

    /**
     * @return array
     */
    public function getRegisteredClasses(): array
    {
        return $this->registeredClasses;
    }

    public function getRegisteredClass(string $key): ?string
    {
        return $this->registeredClasses[$key] ?? null;
    }

    protected function ruleOfUnique(string $name, string $value, string $entity, string $field=null): void
    {
        $field ??= $name;
        $class = $this->getRegisteredClass($entity);
        if (!is_subclass_of($class, EntityInterface::class)) {
            throw SystemException::of(
                'The validation rule over `%s` is with an unregistered class name `%s`.',
                $name, $entity
            );
        }

        $sql = sprintf('SELECT %s FROM %s WHERE %s=:value', $field, $class::entity_name(), $field);

        // Use parameterized query to prevent SQL injection
        if (Db::query($sql, ['value' => $value])->exists()) {
            throw ValidationException::of('Duplicate key value of `%s`.', $field);
        }
    }

}