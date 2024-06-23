<?php
/**
 * Autumn PHP Framework
 *
 * Date:        9/05/2024
 */

namespace Autumn\Database\Interfaces;

interface RecyclableEntityManagerInterface extends EntityManagerInterface, RecyclableRepositoryInterface
{
    public function softDelete(): bool;

    public function resetDelete(): bool;
}