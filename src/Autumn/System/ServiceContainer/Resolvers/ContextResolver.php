<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\System\ServiceContainer\Resolvers;

use Autumn\Interfaces\ContextInterface;
use Autumn\Interfaces\SingletonInterface;
use Autumn\System\ServiceContainer\ParameterResolverInterface;

class ContextResolver implements ParameterResolverInterface
{
    public function resolve(string $parameterName, \ReflectionNamedType $type, \ArrayAccess|array $args = null, array $context = null): mixed
    {
        $class = $type->getName();
        if (is_subclass_of($class, ContextInterface::class)) {
            return $class::context();
        }

        if (is_subclass_of($class, SingletonInterface::class)) {
            return $class::getInstance();
        }

        return null;
    }
}