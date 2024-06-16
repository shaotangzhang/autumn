<?php

namespace Autumn\System\ClassFactory;

use ReflectionProperty;

class Property implements \Stringable
{
    private ?DocComment $docComment = null;

    private string $name;

    private ?Type $type = null;

    private mixed $defaultValue = null;

    private string $defaultValueConstantName = '';

    private bool $hasDefaultValue = false;

    private bool $static = false;

    private bool $readOnly = false;

    private bool $private = false;

    private bool $protected = false;

    private bool $public = false;

    private array $attributes = [];

    public function __construct(string $name, string|Type $type = null, mixed $default = null)
    {
        $this->setName($name);
        $this->type = is_string($type) ? new Type($type) : $type;
        $this->defaultValue = $default;
        $this->hasDefaultValue = func_num_args() > 2;
    }

    public static function fromReflectionProperty(ReflectionProperty $property): static
    {
        $instance = new static($property->getName(), Type::fromReflectionType($property->getType()));
        if ($instance->hasDefaultValue = $property->hasDefaultValue()) {
            $instance->defaultValue = $property->getDefaultValue();
        }
        $instance->public = $property->isPublic();
        $instance->protected = $property->isProtected();
        $instance->private = $property->isPrivate();
        $instance->static = $property->isStatic();
        $instance->readOnly = $property->isReadOnly();
        $instance->docComment = DocComment::parse($property->getDocComment());
        return $instance;
    }

    public function __toString(): string
    {
        $parts = $this->getModifiers();
        $parts[] = $this->type;
        $parts[] = '$' . $this->name;

        if ($this->hasDefaultValue()) {
            if ($this->defaultValueConstantName) {
                $parts[] = '= ' . $this->defaultValueConstantName;
            } else {
                $parts[] = '= ' . var_export($this->defaultValue, true);
            }
        }

        return DocComment::print(1, $this->docComment, ...[...$this->attributes, implode(' ', $parts) . ';']);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(Attribute ...$attributes): void
    {
        array_push($this->attributes, ...$attributes);
    }

    public function getModifiers(): array
    {
        $modifiers = [];

        if ($this->public) {
            $modifiers[] = 'public';
        } elseif ($this->protected) {
            $modifiers[] = 'protected';
        } elseif ($this->private) {
            $modifiers[] = 'private';
        }

        if ($this->static) {
            $modifiers[] = 'static';
        }

        if ($this->readOnly) {
            $modifiers[] = 'readonly';
        }

        return $modifiers;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (!preg_match('/^\w+$/', $name)) {
            throw new \InvalidArgumentException(sprintf('Invalid property name "%s"', $name));
        }

        $this->name = $name;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function getDocComment(bool $autoCreate = false): ?DocComment
    {
        return $this->docComment ??= ($autoCreate ? new DocComment('', [
            'var' => $this->type,
            'static' => $this->static ? '' : null,
            'readonly' => $this->readOnly ? '' : null
        ]) : null);
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue || isset($this->defaultValue) || !empty($this->defaultValueConstantName);
    }

    public function removeDefaultValue(): void
    {
        $this->hasDefaultValue = false;
        $this->defaultValue = null;
        $this->defaultValueConstantName = '';
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(mixed $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
        $this->hasDefaultValue = true;
    }

    public function getDefaultConstantName(): ?string
    {
        return $this->defaultValueConstantName;
    }

    public function setDefaultConstantName(?string $defaultValueConstantName): void
    {
        if ($this->defaultValueConstantName = $defaultValueConstantName ?? '') {
            $this->hasDefaultValue = true;
            $this->defaultValue = null;
        }
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        if ($this->public = $public) {
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
        if ($this->protected = $protected) {
            $this->public = false;
            $this->private = false;
        }
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        if ($this->private = $private) {
            $this->public = false;
            $this->protected = false;
        }
    }

    public function isStatic(): bool
    {
        return $this->static;
    }

    public function setStatic(bool $static): void
    {
        $this->static = $static;
    }

    public function isReadonly(): bool
    {
        return $this->readOnly;
    }

    public function setReadonly(bool $readOnly): void
    {
        $this->readOnly = $readOnly;
    }

    public function isDeprecated(): bool
    {
        return $this->docComment?->isDeprecated() === true;
    }

    public function setDeprecated(bool|string $since): void
    {
        if ($since === false) {
            $this->docComment?->setDeprecated(false);
        } else {
            $this->getDocComment(true)->setDeprecated($since);
        }
    }
}
