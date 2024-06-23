<?php

namespace Autumn\Attributes;

use Attribute;
use Autumn\Caching\Cache;
use Autumn\Interfaces\DecoratorInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class Cacheable implements DecoratorInterface
{
    private ?\ReflectionFunctionAbstract $reflection = null;

    public function __construct(public int $ttl = 3600)
    {
    }

    /**
     * @return \ReflectionFunctionAbstract|null
     */
    public function getReflection(): ?\ReflectionFunctionAbstract
    {
        return $this->reflection;
    }

    /**
     * @param \ReflectionFunctionAbstract|null $reflection
     */
    public function setReflection(?\ReflectionFunctionAbstract $reflection): void
    {
        $this->reflection = $reflection;
    }

    public function withReflection(?\ReflectionFunctionAbstract $reflection): static
    {
        if ($reflection === $this->reflection) {
            return $this;
        }

        $clone = clone $this;
        $clone->reflection = $reflection;
        return $clone;
    }

    public function decorate(callable $callable, array $args = null, array $context = null): mixed
    {
        $key = md5(serialize([
            $this->reflection->getClosureScopeClass()->getName(),
            $this->reflection->getName(),
            $args
        ]));

        $cache = Cache::forClass($this->reflection->getClosureCalledClass()?->getName());
        if ($cache->has($key, $this->ttl)) {
            return $cache->get($key);
        }

        return $cache->set($key, call($callable, $args, $context), $this->ttl);
    }
}
