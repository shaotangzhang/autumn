<?php
namespace Autumn\Extensions\Auth\Models\Session;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\EntityManagerTrait;

class UserSession extends SessionEntity implements RepositoryInterface
{
    use EntityManagerTrait;
}