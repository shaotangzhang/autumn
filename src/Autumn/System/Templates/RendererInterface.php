<?php

namespace Autumn\System\Templates;
interface RendererInterface
{
    public function output(mixed $data, \ArrayAccess|array $args = null, array $context = null): mixed;
}