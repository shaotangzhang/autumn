<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace Autumn\Database\Interfaces;

interface EntityManagerInterface
{
    public static function find(int|array $criteria, array $context = null): ?static;
}