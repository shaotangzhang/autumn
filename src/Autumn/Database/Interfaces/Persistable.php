<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Database\Interfaces;

interface Persistable
{
    public function getId(): int;

    public function isNew(): bool;
}