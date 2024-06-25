<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace Autumn\Database\Traits;

trait RecyclableEntityManagerTrait
{
    use EntityManagerTrait;
    use RecyclableEntityRepositoryTrait;

    public function trash(): bool
    {

    }

    public function restore(): bool
    {

    }
}