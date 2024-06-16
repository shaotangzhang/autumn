<?php

namespace Autumn\System\ClassFactory;

use ReflectionAttribute;
use Stringable;

class Attribute implements Stringable
{
    private string $name;
    private array $arguments;

    public function __construct(string $name, array $arguments = [])
    {
        $this->setName($name);
        
        foreach ($arguments as $offset => $argument) {
            if ($argument instanceof Argument) {
                $this->arguments[] = $argument;
            } else {
                $this->arguments[] = new Argument(is_string($offset) ? $offset : null, $argument);
            }
        }
    }

    public static function fromReflectionAttribute(ReflectionAttribute $reflection): static
    {
        $name = $reflection->getName();
        $args = $reflection->getArguments();
        return new static($name, $args);
    }

    public function __toString(): string
    {
        if(empty($this->arguments)) {
            return '#[' . $this->name . ']';
        }else{
            return '#[' . $this->name . '(' . implode(', ', $this->arguments) . ')]';
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if(str_contains($name, '\\')) {
            $name = '\\' . ltrim($name);
        }
        $this->name = $name;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function addArgument(Argument $argument): void
    {
        $this->arguments[] = $argument;
    }

    public function addArg(string $name, mixed $value = null, bool $variadic = false): void
    {
        $this->arguments[] = new Argument($name, $value, $variadic);
    }
}
