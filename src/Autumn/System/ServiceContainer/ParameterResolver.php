<?php

namespace Autumn\System\ServiceContainer;

use Autumn\Exceptions\NotFoundException;
use Autumn\Interfaces\ContextInterface;
use Autumn\System\Service;

class ParameterResolver extends Service implements ParameterResolverInterface
{
    /**
     * @var array<ParameterResolverInterface>
     */
    private array $resolvers = [];

    protected static function createDefaultInstance(): static
    {
        $instance = new static;
        foreach (glob(__DIR__ . '/Resolvers/*Resolver.php') as $file) {
            $class = __NAMESPACE__ . '\\Resolvers\\' . basename($file, '.php');
            $instance->addResolver($class);
        }
        return $instance;
    }

    public function addResolver(string|ParameterResolverInterface $resolver): static
    {
        if (!in_array($resolver, $this->resolvers, true)) {
            if (!is_subclass_of($resolver, ParameterResolverInterface::class)) {
                throw NotFoundException::of('Parameter resolver `%s` is not found.', is_string($resolver) ? $resolver : $resolver::class);
            }

            $this->resolvers[] = $resolver;
        }

        return $this;
    }

    public function resolve(string $parameterName, \ReflectionNamedType $type, \ArrayAccess|array $args = null, array $context = null): mixed
    {
        if (!$type->isBuiltin()) {

            $class = $type->getName();
            if (isset($context[$class]) && ($context[$class] instanceof $class)) {
                return $context[$class];
            }

            foreach ($this->resolvers as $resolver) {
                if (is_string($resolver)) {
                    $resolver = new $resolver;
                }

                if ($resolver instanceof ParameterResolverInterface) {
                    $value = $resolver->resolve($parameterName, $type, $args, $context);
                    if ($value !== null) {
                        return $value;
                    }
                }
            }
        }

        return null;
    }
}