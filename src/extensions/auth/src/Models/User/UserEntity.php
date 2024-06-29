<?php

namespace Autumn\Extensions\Auth\Models\User;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\Expirable;
use Autumn\Database\Models\RecyclableEntity;
use Autumn\Database\Traits\ExpirableTrait;
use Autumn\Database\Traits\StatusColumnTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Auth\Traits\EmailColumnTrait;
use Autumn\Extensions\Auth\Traits\PasswordColumnTrait;
use Autumn\Extensions\Auth\Traits\UsernameColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, 'username')]
class UserEntity extends RecyclableEntity implements Expirable
{
    use UsernameColumnTrait;
    use PasswordColumnTrait;
    use EmailColumnTrait;
    use TypeColumnTrait;
    use StatusColumnTrait;
    use ExpirableTrait;

    public const ENTITY_NAME = 'auth_users';

    public const DEFAULT_TYPE = 'default';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_DISABLED = 'disabled';
    public const DEFAULT_STATUS = self::STATUS_PENDING;

}