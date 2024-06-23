<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Interfaces;

interface Expirable
{
    public static function column_expired_at(): string;

    public function isExpired(): bool;

    public function getExpiredAt(): int;

    public function getExpiryTime(): ?\DateTimeInterface;
}