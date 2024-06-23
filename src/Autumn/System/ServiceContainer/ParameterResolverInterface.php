<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\System\ServiceContainer;

interface ParameterResolverInterface
{
    public function resolve(string $parameterName, \ReflectionNamedType $type, array|\ArrayAccess $args = null, array $context = null): mixed;
}
