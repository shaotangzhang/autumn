<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Interfaces;

interface Recyclable
{
    public static function column_deleted_at(): string;

    public function isTrashed(): bool;

    public function getDeletedAt(): int;

    public function getDeleteTime(): ?\DateTimeInterface;
}