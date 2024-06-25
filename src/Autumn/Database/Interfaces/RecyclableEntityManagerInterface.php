<?php
/**
 * Autumn PHP Framework
 *
 * Date:        9/05/2024
 */

namespace Autumn\Database\Interfaces;

interface RecyclableEntityManagerInterface extends EntityManagerInterface, RecyclableRepositoryInterface
{
    public function trash(): bool;

    public function restore(): bool;
}