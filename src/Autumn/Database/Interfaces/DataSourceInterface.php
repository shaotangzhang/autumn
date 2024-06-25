<?php
/**
 * Autumn PHP Framework
 *
 * Date:        29/05/2024
 */

namespace Autumn\Database\Interfaces;

interface DataSourceInterface extends \IteratorAggregate
{
    public function fetch(array $args = [], array $context = null): static;
}