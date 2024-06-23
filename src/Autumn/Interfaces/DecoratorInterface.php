<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Interfaces;

interface DecoratorInterface
{
    public function decorate(callable $callable, array $args = null, array $context = null): mixed;
}