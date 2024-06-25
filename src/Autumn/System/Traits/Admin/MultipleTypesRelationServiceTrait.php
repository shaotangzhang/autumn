<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/05/2024
 */

namespace Autumn\System\Traits\Admin;

use Autumn\App;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Models\AbstractEntity;
use Autumn\Database\Models\Relation;
use Autumn\Exceptions\SystemException;
use Autumn\System\ClassFile;
use Autumn\System\Reflection;

trait MultipleTypesRelationServiceTrait // implements RelationServiceInterface
{
    public static function forType(string $type): self
    {
        static $instances;

        if (!isset($instances[static::class][$type])) {
            if (defined($const = static::class . '::DEFAULT_TYPE')) {
                if (constant($const) === $type) {
                    return $instances[static::class][$type] = new static;
                }
            }

            if (!($extendClass = Reflection::baseClassOf(static::class))) {
                throw SystemException::of('Unable to initialize an anonymous class %s with type: %s.', static::class, $type);
            }

            $className = ClassFile::forClassWithConstants($extendClass, [
                'DEFAULT_TYPE' => $type
            ]);

            $instances[static::class][$type] = App::factory($className);
        }

        return $instances[static::class][$type];
    }

    public static function forRelation(Relation|string $relationClass, string $alias, string $primaryAlias = null): self
    {
        static $instances;

        if (!isset($instances[$relationClass][$alias][$primaryAlias])) {
            if (!is_subclass_of($relationClass, Relation::class, true)) {
                throw SystemException::of('Invalid relation class `%s`.', $relationClass);
            }

            if (!($extendClass = Reflection::baseClassOf(static::class))) {
                throw SystemException::of('Unable to initialize an anonymous class %s with change to relation: %s.', static::class, $relationClass);
            }

            if (defined($const = static::class . '::DEFAULT_TYPE')) {
                $type = constant($const);
            } else {
                $type = null;
            }

            $className = ClassFile::forClass($extendClass, $relationClass . '::' . $type,
                function (ClassFile $file) use ($type, $relationClass, $alias, $primaryAlias) {
                    $file->addConstant('DEFAULT_TYPE', $type);
                    $file->addConstantWithClass('RELATION', $relationClass);

                    $file->addMethod('getList', [
                        sprintf("\$context['primaryAlias'] = '%s';", $primaryAlias),
                        sprintf("\$context['relationAlias'] = '%s';", $alias),
                        "\$context['relation'] = static::RELATION;",
                        "return parent::getList(\$context);"
                    ], [
                        'context' => ['types' => 'array', 'default' => null]
                    ], 'public', '\\' . RepositoryInterface::class);
                }
            );

            $instances[$relationClass][$alias][$primaryAlias] = App::factory($className);
        }

        return $instances[$relationClass][$alias][$primaryAlias];
    }

    public static function forClass(string $class): self
    {
        $type = is_subclass_of($class, AbstractEntity::class)
            ? $class::entity_name() : md5($class);

        return static::forType($type);
    }
}