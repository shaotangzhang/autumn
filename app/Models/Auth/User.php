<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/29
 */

namespace App\Models\Auth;

use App\Database\Auth\UserEntity;
use Autumn\App;
use Autumn\Security\Crypto\PasswordEncoderFactory;
use Autumn\Security\Interfaces\PasswordEncoder;
use Autumn\System\Request;
use Autumn\Validation\Assert;

class User extends UserEntity
{
    public const DEFAULT_STATUS = 'pending';
    public const DEFAULT_TYPE = 'standard';

    public static function getPasswordEncoder(): PasswordEncoder
    {
        static $g;
        if (!$g) $g = App::factory(PasswordEncoder::class) ?: PasswordEncoderFactory::createDelegatingPasswordEncoder();
        return $g;
    }

    public static function encryptPassword(string $password): string
    {
        return static::getPasswordEncoder()->encode($password);
    }

    public static function verifyPassword(string $password, string $hash): string
    {
        return static::getPasswordEncoder()->matches($password, $hash);
    }

    protected function onPersist(): void
    {
        parent::onPersist();

        $this->validateUsername();
        $this->validatePassword();
        $this->validateEmail();

        $this->setPassword(static::encryptPassword($this->getPassword()));

        if (!$this->getStatus()) {
            $this->setStatus(static::DEFAULT_STATUS);
        }

        if (!$this->getType()) {
            $this->setType(static::DEFAULT_TYPE);
        }

        if (!$this->getIp()) {
            $this->setIp(Request::ip());
        }
    }

    public function validateUsername(): void
    {
        Assert::isBetween(strlen($this->getUsername()), 3, 40, 'Username must be 3-40 characters');
    }

    public function validatePassword(): void
    {
        Assert::isMoreThan(strlen($this->getPassword()), 3, 'Password must be more than characters');
    }

    public function validateEmail(): void
    {
        Assert::isEmail($this->getEmail(), 'Invalid email');
    }
}