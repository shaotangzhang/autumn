<?php

namespace Autumn\System\ClassFactory;

use ReflectionMethod;
use ReflectionParameter;

class Parameter implements \Stringable
{
    private string $name;

    private mixed $defaultValue = null;
    private string $defaultValueConstantName = '';

    private ?Type $type = null;


    public function __construct(
        string $name,
        string|Type $type = null,
        private bool $optional = false,
        private bool $variadic = false,
        private bool $passedByReference = false,
        mixed $defaultValue = null,
        private bool $defaultValueIsConstantName = false,
        private ?string $comment = null,
        private array $attributes = []
    ) {
        $this->setName($name);
        $this->setType($type);

        if ($this->defaultValueIsConstantName && is_string($defaultValue)) {
            $this->defaultValueConstantName = $defaultValue;
        } else {
            $this->defaultValue = $defaultValue;
        }
    }

    public static function fromReflectionParameter(ReflectionParameter $reflection): static
    {
        $instance = new static(
            $reflection->getName(),
            Type::fromReflectionType($reflection->getType()),
            $reflection->isOptional(),
            $reflection->isVariadic(),
            $reflection->isPassedByReference(),
            $reflection->getDefaultValueConstantName() ?? $reflection->getDefaultValue(),
            !empty($reflection->getDefaultValueConstantName())
        );

        foreach ($reflection->getAttributes() as $attribute) {
            $instance->attributes[] = Attribute::fromReflectionAttribute($attribute);
        }

        return $instance;
    }


    public function __toString(): string
    {
        $parts = [];
        if ($this->type) {
            $parts[] = $this->type;
        }

        if ($this->passedByReference) {
            $parts[] = '&$' . $this->name;
        } elseif ($this->variadic) {
            $parts[] = '...$' . $this->name;
        } else {
            $parts[] = '$' . $this->name;
        }

        if ($this->optional) {
            if ($this->defaultValueIsConstantName) {
                $parts[] = '= ' . $this->defaultValueConstantName;
            } else {
                $parts[] = '= ' . var_export($this->defaultValue, true);
            }
        }

        return DocComment::print(0, null, ...[...$this->attributes, implode(' ', $parts)]);
    }
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(Attribute ...$attributes): void
    {
        array_push($this->attributes, ...$attributes);
    }
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $name)) {
            throw new \InvalidArgumentException('Parameter name must be a valid variable name');
        }

        $this->name = $name;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function setType(string|Type|null $type): void
    {
        if (is_string($type)) {
            $type = new Type($type);
        }
        $this->type = $type;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }
    public function setOptional(bool $optional): void
    {
        $this->optional = $optional;
    }

    public function isVariadic(): bool
    {
        return $this->variadic;
    }

    public function setVariadic(bool $variadic): void
    {
        $this->variadic = $variadic;
    }

    public function isPassedByReference(): bool
    {
        return $this->passedByReference;
    }
    public function setPassedByReference(bool $passedByReference): void
    {
        $this->passedByReference = $passedByReference;
    }

    public function isDefaultValueConstantName(): bool
    {
        return $this->defaultValueIsConstantName;
    }

    public function getDefaultValueConstantName(): ?string
    {
        return $this->defaultValueConstantName;
    }

    public function setDefaultValueConstantName(?string $constantName): void
    {
        if ($constantName) {
            $this->defaultValueConstantName = $constantName;
            $this->defaultValueIsConstantName = true;
        }
    }
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}
