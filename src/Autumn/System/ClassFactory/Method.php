<?php

namespace Autumn\System\ClassFactory;

use ReflectionMethod;
use Stringable;

class Method implements Stringable
{
    private string $name;
    private array $parameters = [];
    private ?Type $returnType = null;
    private bool $static = false;
    private bool $final = false;
    private bool $abstract = false;
    private bool $public = false;
    private bool $protected = false;
    private bool $private = false;
    private ?DocComment $docComment = null;

    private array $codes = [];

    private array $attributes = [];

    public function __construct(string $name, string ...$codes)
    {
        $this->setName($name);
        $this->codes = $codes;
    }

    public static function fromReflectionMethod(ReflectionMethod $reflection): static
    {
        $instance = new static($reflection->getName(), ...static::extractMethodCodes($reflection));

        $instance->setPublic($reflection->isPublic());
        $instance->setPrivate($reflection->isPrivate());
        $instance->setProtected($reflection->isProtected());
        $instance->setStatic($reflection->isStatic());
        $instance->setAbstract($reflection->isAbstract());
        $instance->setFinal($reflection->isFinal());
        $instance->setReturnType($reflection->getReturnType());

        if ($comment = $reflection->getDocComment()) {
            $instance->docComment = DocComment::parse($comment);
        }

        foreach ($reflection->getAttributes() as $attribute) {
            $instance->attributes[] = Attribute::fromReflectionAttribute($attribute);
        }

        foreach ($reflection->getParameters() as $parameter) {
            $instance->parameters[] = Parameter::fromReflectionParameter($parameter);
        }

        return $instance;
    }

    public static function extractMethodCodes(ReflectionMethod $reflection): array
    {
        if ($code = static::extractMethodCode($reflection)) {
            $start = strpos($code, '{') + 1;
            $end = strrpos($code, '}');
            $code = substr($code, $start, $end - $start);

            $code = trim($code, "\r\n");

            return preg_split('/\R/', $code);
        }
        return [];
    }

    public static function extractMethodCode(ReflectionMethod $reflection): string
    {
        static $cache = [];

        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();

        $fileContent = $cache[$filename] ??= file($filename);
        $methodCode = implode("", array_slice($fileContent, $startLine - 1, $endLine - $startLine + 1));

        return $methodCode;
    }

    public function __toString(): string
    {
        $parts = $this->getModifiers();

        $parts[] = 'function';
        $parts[] = $this->getName();
        $parts = [implode(' ', $parts) . '(' . implode(', ', $this->getParameters()) . ')'];

        if ($this->getReturnType()) {
            $parts[0] .= ':';
            $parts[] = $this->getReturnType();
        }

        if ($this->abstract) {
            $lines = implode(' ', $parts) . ';';
            return DocComment::print(1, $this->docComment, $lines);
        }

        return DocComment::print(1, $this->docComment, implode(' ', $parts)) . PHP_EOL
            . implode(PHP_EOL, ["\t{",  implode(PHP_EOL, $this->codes),  "\t}"]);
    }

    public function getCodes(): array
    {
        return $this->codes;
    }

    public function append(string ...$codes): void
    {
        foreach ($codes as $code) {
            $this->codes[] = "\t\t" . $code;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name)) {
            throw new \InvalidArgumentException('Invalid method name: ' . $name);
        }

        $this->name = $name;
    }

    public function addParameter(string|Parameter $parameter): void
    {
        if (is_string($parameter)) {
            $parameter = new Parameter($parameter);
        }

        $this->parameters[] = $parameter;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setReturnType(string|Type|null $returnType): void
    {
        if (is_string($returnType)) {
            $returnType = new Type($returnType);
        }

        $this->returnType = $returnType;
    }

    public function getReturnType(): ?Type
    {
        return $this->returnType;
    }

    public function isStatic(): bool
    {
        return $this->static;
    }

    public function setStatic(bool $static): void
    {
        $this->static = $static;
    }

    public function isFinal(): bool
    {
        return $this->final;
    }

    public function setFinal(bool $final): void
    {
        $this->final = $final;
    }

    public function isAbstract(): bool
    {
        return $this->abstract;
    }

    public function setAbstract(bool $abstract): void
    {
        $this->abstract = $abstract;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        if ($public) {
            $this->public = true;
            $this->protected = false;
            $this->private = false;
        }
    }

    public function isProtected(): bool
    {
        return $this->protected;
    }

    public function setProtected(bool $protected): void
    {
        if ($protected) {
            $this->public = false;
            $this->protected = true;
            $this->private = false;
        }
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        if ($private) {
            $this->public = false;
            $this->protected = false;
            $this->private = true;
        }
    }

    public function isDeprecated(): bool
    {
        return $this->docComment?->isDeprecated() ?? false;
    }

    public function setDeprecated(bool|string $deprecated): void
    {
        if ($deprecated === false) {
            $this->docComment?->setDeprecated(false);
        } else {
            $this->getDocComment(true)->setDeprecated($deprecated);
        }
    }

    public function getDocComment(bool $autoCreate = false): ?DocComment
    {
        if ($autoCreate && !$this->docComment) {
            $this->docComment = new DocComment;
            foreach ($this->parameters as $parameter) {
                $this->docComment->addParameter($parameter);
            }
            $this->docComment->addAnnotation('return', $this->returnType);
        }

        return $this->docComment;
    }

    public function setDocComment(string|DocComment $comment): void
    {
        if (is_string($comment)) {
            $this->getDocComment(true)->setComment($comment);
        } else {
            $this->docComment = $comment;
        }
    }

    private function getModifiers(): array
    {
        $modifiers = [];

        if ($this->abstract) {
            $modifiers[] = 'abstract';
        } elseif ($this->final) {
            $modifiers[] = 'final';
        }

        if ($this->static) {
            $modifiers[] = 'static';
        }

        if ($this->public) {
            $modifiers[] = 'public';
        } elseif ($this->protected) {
            $modifiers[] = 'protected';
        } elseif ($this->private) {
            $modifiers[] = 'private';
        }

        return $modifiers;
    }

    public function isConstructor(): bool
    {
        return $this->name === '__construct';
    }
}
