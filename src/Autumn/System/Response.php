<?php

namespace Autumn\System;

class Response
{
    public function __construct(private mixed $content = null, int $statusCode = null, string $reasonPhrase = null, string $protocolVersion = null)
    {
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function send(): void
    {
        echo $this->getContent();
    }
}
