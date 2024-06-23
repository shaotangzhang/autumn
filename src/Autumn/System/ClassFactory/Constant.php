<?php

namespace Autumn\System\ClassFactory;

use ReflectionClassConstant;
use Stringable;

class Constant implements Stringable
{
    private ?DocComment $docComment = null;

    private bool $final = false;

    private bool $private = false;

    private bool $protected = false;

    private bool $public = false;

    private bool $enumCase = false;

    public function __construct(
        private string $name,
        private mixed $value,
        private bool $valueIsConstantName = false,
        string|array|DocComment $comment = null
    ) {

        if (is_string($comment)) {
            $this->getDocComment(true)->setComment($comment);
        } elseif (is_array($comment)) {
            $this->docComment = new DocComment('', $comment);
        } else {
            $this->docComment = $comment;
        }
    }

    public static function fromReflectionClassConstant(ReflectionClassConstant $reflection): static
    {
        $instance = new static($reflection->getName(), $reflection->getValue());

        $instance->enumCase = $reflection->isEnumCase();
        $instance->setFinal($reflection->isFinal());
        $instance->setPublic($reflection->isPublic());
        $instance->setProtected($reflection->isProtected());
        $instance->setPrivate($reflection->isPrivate());

        if ($comment = $reflection->getDocComment()) {
            $instance->docComment = DocComment::parse($comment);
        }

        return $instance;
    }

    public function __toString(): string
    {
        $parts = $this->getModifiers();
        $parts[] = $this->enumCase ? 'case' : 'const';

        $parts[] = $this->name;
        $parts[] = '=';

        if ($this->valueIsConstantName && is_string($this->value)) {
            $parts[] = $this->value;
        } else {
            $parts[] = var_export($this->value, true);
        }

        return DocComment::print(1, $this->docComment, implode(' ', $parts) . ';');
    }

    public function getModifiers(): array
    {
        $modifiers = [];

        if ($this->enumCase) {
            return $modifiers;
        }

        if ($this->final) {
            $modifiers[] = 'final';
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

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(bool|int|float|string|array|null $value): void
    {
        $this->value = $value;

        if (!is_string($value)) {
            $this->valueIsConstantName = false;
        }
    }

    public function isEnumCase(): bool
    {
        return $this->enumCase;
    }

    public function isValueConstantName(): string
    {
        return $this->valueIsConstantName;
    }

    public function setValueConstantName(?string $constantName): void
    {
        if ($this->value = $constantName) {
            $this->valueIsConstantName = true;
        } else {
            $this->valueIsConstantName = false;
        }
    }

    public function getDocComment(bool $autoCreate = false): ?DocComment
    {
        if ($autoCreate && !$this->docComment) {
            $this->docComment = new DocComment;
        }
        return $this->docComment;
    }

    public function setDocComment(string|DocComment|null $comment): void
    {
        if (is_string($comment)) {
            $this->docComment = new DocComment($comment);
        }

        $this->docComment = $comment;
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

    public function isFinal(): bool
    {
        return $this->final;
    }

    public function setFinal(bool $final): void
    {
        $this->final = $final;
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
