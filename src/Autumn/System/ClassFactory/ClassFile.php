<?php

namespace Autumn\System\ClassFactory;

use ReflectionClass;
use Stringable;

class ClassFile implements Stringable
{
    public const CLASS_TYPE = 'class';

    private string $namespace = '';

    private string $className;

    private ?string $extends;

    private ?DocComment $docComment = null;

    private array $interfaces;

    private array $imports = [];    // use import;

    private array $traits = [];       // use traits;

    private array $constants = [];

    private array $properties = [];

    private array $methods = [];

    private bool $final = false;
    private bool $abstract = false;
    private bool $readOnly = false;

    public function __construct(string $className, ?string $extends = null, string ...$interfaces)
    {
        $this->extends = $extends;
        $this->interfaces = array_map('self::formatClassName', $interfaces);
        $this->className = static::getShortClassName($className, $this->namespace);
    }
    
    // /**
    //  * This feature is not ready yet.
    //  * 
    //  * @deprecated version
    //  */
    // private static function extractImportsFromFile(string $file): array
    // {
    //     if (is_file($file)) {
    //         if ($contents = file_get_contents($file)) {
    //             return static::extractImportsFromCode($contents);
    //         }
    //     }

    //     return [];
    // }

    // /**
    //  * This feature is not ready yet.
    //  * 
    //  * @deprecated version
    //  */
    // private static function extractImportsFromCode(string $code): array
    // {   
    //     $pattern = '/^\s*use\s+([a-zA-Z\\\]+)(?:\s+as\s+([a-zA-Z]+))?;/';
    //     preg_match_all($pattern, $code, $matches);

    //     $imports = [];
    //     foreach ($matches[1] as $index => $import) {
    //         $alias = $matches[2][$index] ?? null;
    //         if (!$alias) {
    //             $alias = static::getShortClassName($import);
    //         }
    //         $imports[$alias] = $import;
    //     }
    //     return $imports;
    // }

    public static function getShortClassName(string $className, string &$namespace = null): string
    {
        $pos = strrpos($className, '\\');
        if ($pos !== false) {
            $namespace = static::formatClassName(substr($className, 0, $pos), false);
            $className = substr($className, $pos + 1);
        }
        return $className;
    }

    public static function formatClassName(?string $className, bool $full = true): ?string
    {
        if (empty($className)) {
            return $className;
        }

        if ($full) {
            $className = ltrim($className, '/\\');
            $className = '\\' . $className;
        } elseif (\str_contains($className, '\\')) {
            $className = '\\' . ltrim($className, '/\\');
        }

        return $className;
    }

    public function __toString(): string
    {
        $lines = [];

        if ($this->namespace) {
            $namespace = ltrim($this->namespace, '\\');
            $lines[] = "namespace $namespace;";
            $lines[] = '';
        }

        if ($this->imports) {
            foreach ($this->imports as $alias => $import) {
                $import = ltrim(static::formatClassName($import, false), '\\');
                if (is_string($alias) && ($alias !== $import) && !str_ends_with($import, '\\' . $alias)) {
                    $lines[] = "use $import as $alias;";
                } else {
                    $lines[] = "use $import;";
                }
            }
            $lines[] = '';
        }

        if ($this->docComment) {
            $lines[] = $this->docComment;
        }

        $parts = $this->getModifiers();
        $parts[] = static::CLASS_TYPE;
        $parts[] = $this->className;
        if ($this->extends) {
            $parts[] = 'extends';
            $parts[] = $this->getClassAlias($this->extends, true);
        }
        $lines[] = implode(' ', $parts);

        if (!empty($this->interfaces)) {
            $lines[] = "\timplements " . implode(', ', array_map(fn ($c) => $this->getClassAlias($c, true), $this->interfaces));
        }

        $lines[] = '{';

        $notEmpty = false;

        if (!empty($this->traits)) {
            $lines = array_merge($lines, $this->traits);
            $notEmpty = true;
        }

        if ($this->constants) {
            uasort($this->constants, function ($b, $a) {
                return $a->isFinal() <=> $b->isFinal()
                    ?: $a->isPublic() <=> $b->isPublic()
                    ?: $a->isProtected() <=> $b->isProtected()
                    ?: $a->isPrivate() <=> $b->isPrivate();
            });

            if ($notEmpty) {
                $lines[] = '';
            }
            $lines = array_merge($lines, $this->constants);
            $notEmpty = true;
        }

        if ($this->properties) {
            uasort($this->properties, function ($b, $a) {
                return $a->isStatic() <=> $b->isStatic()
                    ?: $a->isPublic() <=> $b->isPublic()
                    ?: $a->isProtected() <=> $b->isProtected()
                    ?: $a->isPrivate() <=> $b->isPrivate();
            });

            if ($notEmpty) {
                $lines[] = '';
            }
            $lines = array_merge($lines, $this->properties);
            $notEmpty = true;
        }

        uasort($this->methods, function ($b, $a) {
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

        foreach ($this->methods as $method) {

            if ($notEmpty) {
                $lines[] = '';
            }
            $lines[] = $method;
            $notEmpty = true;
        }

        $lines[] = '}';
        return implode(PHP_EOL, $lines);
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $className)) {
            throw new \InvalidArgumentException('Invalid class name: ' . $className);
        }

        $this->className = $className;
    }

    public function getExtends(): ?string
    {
        return $this->extends;
    }

    public function setExtends(string $extends): void
    {
        $this->extends = static::formatClassName($extends, true);
    }

    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    public function implement(string ...$interfaces): void
    {
        $this->interfaces = array_unique(array_merge($this->interfaces, array_map(function (string $interface) {
            return static::formatClassName($interface, true);
        }, $interfaces)));
    }

    public function getTraits(): array
    {
        return $this->traits;
    }

    public function use(string $trait, string ...$others): TraitUsage
    {
        return $this->traits[] = new TraitUsage($trait, ...$others);
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function addProperty(string|Property $property): void
    {
        if (is_string($property)) {
            $property = new Property($property);
        }

        $this->properties[$property->getName()] = $property;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function addMethod(string|Method $method): void
    {
        if (is_string($method)) {
            $method = new Method($method);
        }

        $this->methods[$method->getName()] = $method;
    }

    public function getConstants(): array
    {
        return $this->constants;
    }

    public function addConstant(string|Constant $constant, mixed $value = null): void
    {
        if (is_string($constant)) {
            $constant = new Constant($constant, $value);
        }
        $this->constants[$constant->getName()] = $constant;
    }

    public function getImports(): array
    {
        return $this->imports;
    }

    public function import(string $import, string $alias = null): void
    {
        $className = static::formatClassName($import, true);
        if (empty($alias)) {
            $alias = static::getShortClassName($className);
        }

        if (isset($this->imports[$alias])) {
            throw new \InvalidArgumentException("Alias $alias is already used");
        }

        $this->imports[$alias] = $className;
    }

    public function getClassAlias(string $className, bool $autoImport = false): string
    {
        $className = static::formatClassName($className, true);
        $alias = static::getShortClassName($className);
        if (isset($this->imports[$alias])) {
            if ($this->imports[$alias] === $className) {
                return $alias;
            }
        } elseif ($autoImport) {
            $this->imports[$alias] = $className;
            return $alias;
        }
        return $className;
    }

    public function getModifiers(): array
    {
        $modifiers = [];
        if ($this->abstract) {
            $modifiers[] = 'abstract';
        } elseif ($this->final) {
            $modifiers[] = 'final';
        }
        if ($this->readOnly) {
            $modifiers[] = 'readonly';
        }
        return $modifiers;
    }

    public function isFinal(): bool
    {
        return $this->final;
    }

    public function isAbstract(): bool
    {
        return $this->abstract;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function getDocComment(bool $autoCreate = false): ?DocComment
    {
        if ($autoCreate && !$this->docComment) {
            $this->docComment = new DocComment('Class ' . $this->className, [
                'package' => $this->namespace ?: null,
                'since' => date('Y-m-d')
            ]);
        }

        return $this->docComment;
    }

    public function setDocComment(string|DocComment|null $comment, bool $autoCreate = false): void
    {
        if (is_string($comment)) {
            if (!$autoCreate && !$this->docComment) {
                $this->docComment = new DocComment($comment);
            } else {
                $this->getDocComment(true)->setComment($comment);
            }
        } else {
            $this->docComment = $comment;
        }
    }
}
