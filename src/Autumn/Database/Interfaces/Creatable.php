<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Interfaces;

interface Creatable extends Persistable
{
    public static function column_created_at(): string;

    public function getCreatedAt(): int;

    public function getCreateTime(): ?\DateTimeInterface;
}