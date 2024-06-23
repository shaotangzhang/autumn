<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/05/2024
 */

namespace Autumn\Interfaces;

interface ModelInterface
{
    public static function from(array $data): static;

    public function fromArray(array $data): static;
}