<?php

namespace Autumn\System\ClassFactory;

use Stringable;

class InterfaceFile implements Stringable
{
    private string $namespace = '';

    private string $className;

    private array $extends;

    private ?DocComment $docComment = null;

    private array $imports = [];    // use import;

    private array $constants = [];

    private array $methods = [];

    public function __construct(string $className, string ...$extends)
    {
        $this->extends = array_map('self::formatClassName', $extends);
        $this->className = static::getShortClassName($className, $this->namespace);
    }

    public static function getShortClassName(string $className, string &$namespace = null): string
    {
        $pos = strrpos($className, '\\');
        if ($pos !== false) {
            $namespace = static::formatClassName(substr($className, 0, $pos), true);
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

        $parts[] = 'interface';
        $parts[] = $this->className;
        if ($this->extends) {
            $parts[] = 'extends';
            $parts[] = implode(', ', array_map(fn ($c) => $this->getClassAlias($c, true), $this->extends));
        }
        $lines[] = implode(' ', $parts);

        $lines[] = '{';

        $notEmpty = false;

        if ($this->constants) {
            uasort($this->constants, function ($b, $a) {
                return $a->isStatic() <=> $b->isStatic()
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
            $method->setAbstract(true);
            $lines[] = str_replace('abstract ', '', (string)$method);
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

    public function getExtends(): array
    {
        return $this->extends;
    }

    public function extends(string ...$extends): void
    {
        $this->extends = array_unique(array_merge($this->extends, array_map(function (string $interface) {
            return static::formatClassName($interface, true);
        }, $extends)));
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
            if ($className !== "\\$alias") {
                $this->imports[$alias] = $className;
                return $alias;
            }
        }
        return $className;
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
