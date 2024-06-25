<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Interfaces;

interface Persistable
{
    public static function column_primary_key(): string|array;

    public function getId(): int;

    public function isNew(): bool;

    public function pk(): int|array;
}