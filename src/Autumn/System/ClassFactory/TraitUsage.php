<?php

namespace Autumn\System\ClassFactory;


class TraitUsage implements \Stringable
{
    private array $traits;

    private array $modifications = [];

    private ?ClassFile $classFile = null;

    public function __construct(string ...$traits)
    {
        $this->traits = $traits;
    }

    public function __toString(): string
    {
        $parts = 'use ' . implode(', ', $this->traits);

        if (!empty($this->modifications)) {
            $parts .= '{';

            foreach ($this->modifications as $modification) {
                $parts .= "\r\n\t\t" . implode(' ', $modification) . ';';
            }

            $parts .= "\r\n\t}";
            return DocComment::print(1, null, $parts);
        } else {
            return DocComment::print(1, null, $parts . ';');
        }
    }

    public function setClassFile(?ClassFile $classFile): void
    {
        $this->classFile = $classFile;
    }

    public function getModifiers(): array
    {
        return $this->modifications;
    }

    public function modify(string $name, string $modifier = null, string $alias = null): void
    {
        $this->modifications[$name] = [$name, 'as', trim("$modifier $alias")];
    }

    public function select(string $trait, string $method, string ...$insteadOf): void
    {
        $name = "$trait::$method";
        $this->modifications[$name] = [$name, 'insteadof', implode(', ', $insteadOf)];
    }
}
