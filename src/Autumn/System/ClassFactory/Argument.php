<?php

namespace Autumn\System\ClassFactory;

class Argument implements \Stringable
{
    public function __construct(
        private ?string $name,
        private mixed   $value = null,
        private bool    $variadic = false,
        private bool    $constant = false
    )
    {
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

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function isVariadic(): bool
    {
        return $this->variadic;
    }

    /**
     * @param bool $variadic
     */
    public function setVariadic(bool $variadic): void
    {
        $this->variadic = $variadic;
    }

    public function isConstant(): bool
    {
        return $this->constant;
    }

    /**
     * @param bool $constant
     */
    public function setConstant(bool $constant): void
    {
        $this->constant = $constant;
    }
}
