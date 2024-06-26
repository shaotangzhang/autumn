<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace App\Models\User;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Auth\Models\UserEntity;

class User extends UserEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;

}