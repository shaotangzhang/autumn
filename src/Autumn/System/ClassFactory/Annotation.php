<?php

namespace Autumn\System\ClassFactory;

class Annotation implements \Stringable
{
    public function __construct(private string $name, private string $content)
    {
    }

    public function __toString(): string
    {
        return '@' . $this->name . ' ' . $this->content;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}