<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Interfaces;

interface Updatable
{
    public static function column_updated_at(): string;

    public function getUpdatedAt(): int;

    public function getUpdateTime(): ?\DateTimeInterface;

    public function setUpdatedAt(int|float|string|\DateTimeInterface $time): void;
}