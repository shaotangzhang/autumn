<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/06/2024
 */

namespace Autumn\System\Templates;

interface RendererInterface
{
    public function output(mixed $data, array|\ArrayAccess $args = null, array $context = null): mixed;
}