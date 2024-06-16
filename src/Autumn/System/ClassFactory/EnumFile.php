<?php

namespace Autumn\System\ClassFactory;

use BackedEnum;

class EnumFile extends ClassFile
{
    private array $cases = [];

    public function __construct(string $className, private bool $backedType = false, string ...$interfaces)
    {
        if ($this->backedType) {
            $extends = BackedEnum::class;
        } else {
            $extends = null;
        }

        parent::__construct($className, $extends, ...$interfaces);
    }

    public function isBackedType(): bool
    {
        return $this->backedType;
    }

    public function setBackedType(bool $backedType): void
    {
        $this->backedType = $backedType;
    }

    public function getCases(): array
    {
        return $this->cases;
    }

    public function case(string $name, int|string $value = null): void
    {
        if ($this->backedType && ($value === null)) {
            throw new \InvalidArgumentException('Backed enum cases must have a value.');
        }

        $this->cases[$name] = $value;
    }

    public function __toString(): string
    {
        $lines = [];

        if ($namespace = $this->getNamespace()) {
            $namespace = ltrim($namespace, '\\');
            $lines[] = "namespace $namespace;";
            $lines[] = '';
        }

        if ($imports = $this->getImports()) {
            foreach ($imports as $alias => $import) {
                $import = ltrim(static::formatClassName($import, false), '\\');
                if (is_string($alias) && ($alias !== $import) && !str_ends_with($import, '\\' . $alias)) {
                    $lines[] = "use $import as $alias;";
                } else {
                    $lines[] = "use $import;";
                }
            }
            $lines[] = '';
        }

        if ($docComment = $this->getDocComment()) {
            $lines[] = $docComment;
        }

        $parts = $this->getModifiers();
        $parts[] = 'enum';
        $parts[] = $this->getClassName();
        if ($this->isBackedType()) {
            $parts[] = 'extends';
            $parts[] = '\\' . BackedEnum::class;
        }
        $lines[] = implode(' ', $parts);

        if (!empty($interfaces = $this->getInterfaces())) {
            $lines[] = "implements " . implode(', ', array_map(fn($c) => $this->getClassAlias($c, true), $interfaces));
        }

        $lines[] = '{';

        $notEmpty = false;

        if ($constants = $this->getConstants()) {
            uasort($constants, function ($b, $a) {
                return $a->isStatic() <=> $b->isStatic()
                    ?: $a->isPublic() <=> $b->isPublic()
                    ?: $a->isProtected() <=> $b->isProtected()
                    ?: $a->isPrivate() <=> $b->isPrivate();
            });

            if ($notEmpty) {
                $lines[] = '';
            }
            $lines = array_merge($lines, $constants);
            $notEmpty = true;
        }

        if ($this->cases) {
            foreach ($this->cases as $case => $value) {
                if ($this->isBackedType() && $value !== null) {
                    $value = var_export($value, true);
                    $lines[] = "\tcase $case = $value;";
                } else {
                    $lines[] = "\tcase $case;";
                }
            }
        }

        if ($methods = $this->getMethods()) {
            uasort($methods, function ($b, $a) {
                if ($a->isConstructor()) {
                    return -1;
                }
                if ($b->isConstructor()) {
                    return 1;
                }
                return $a->isStatic() <=> $b->isStatic()
                    ?: $a->isPublic() <=> $b->isPublic()
                    ?: $a->isProtected() <=> $b->isProtected()
                    ?: $a->isPrivate() <=> $b->isPrivate();
            });

            foreach ($methods as $method) {
                if ($notEmpty) {
                    $lines[] = '';
                }
                $lines[] = $method;
                $notEmpty = true;
            }
        }

        $lines[] = '}';
        return implode(PHP_EOL, $lines);
    }
}
