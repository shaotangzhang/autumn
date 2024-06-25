<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Interfaces;

interface StatusInterface
{
    public static function defaultStatus(): string;

    public function getStatus(): string;
}