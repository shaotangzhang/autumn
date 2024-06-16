<?php

namespace Autumn\System\ClassFactory;

class Argument implements \Stringable
{
    public function __construct(
        private ?string $name,
        private mixed $value = null,
        private bool $variadic = false,
        private bool $constant = false
    ) {
    }

    public function __toString(): string
    {
        $value = ($this->constant && is_string($this->value))
            ? $this->value
            : var_export($this->value, true);

        if (is_iterable($this->value)) {
            $variadic = $this->isVariadic() ? '...' : '';
            $value = $variadic . $value;
        }

        if ($this->name) {
            return $this->name . ': ' . $value;
        }

        return $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
    public function isVariadic(): bool
    {
        return $this->variadic;
    }

    public function isConstant(): bool
    {
        return $this->constant;
    }
}
