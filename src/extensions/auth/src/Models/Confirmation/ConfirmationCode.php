<?php

namespace Autumn\Extensions\Auth\Models\Confirmation;

use Autumn\Database\Db;
use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Exceptions\ValidationException;
use Autumn\Extensions\Auth\Models\User\User;

class ConfirmationCode extends ConfirmationCodeEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;

    public static function generate(int $userId, string $purpose, int $length, bool $numbersOnly = false, bool $lettersOnly = false): static
    {
        if ($numbersOnly) {
            $code = static::randomNumber($length);
        } elseif ($lettersOnly) {
            $code = static::randomLetters($length);
        } else {
            $code = static::randomCode($length);
        }

        return static::from([
            'code' => $code,
            'type' => $purpose,
            'user_id' => $userId,
            'status' => static::DEFAULT_STATUS,
            'deleted_at' => strtotime('+1 day')
        ]);
    }

    public static function randomInt(int $min = 0, int $max = PHP_INT_MAX): int
    {
        try {
            return random_int($min, $max);
        } catch (\Exception) {
            return mt_rand($min, $max);
        }
    }

    public static function randomBytes(int $length): string
    {
        try {
            return random_bytes($length);
        } catch (\Exception) {
            return openssl_random_pseudo_bytes($length) ?: '';
        }
    }

    public static function randomCode(int $length): string
    {
        if ($length < 2 || $length > 32) {
            throw ValidationException::of('The random code must have a length between 2 and 32 characters.');
        }

        try {
            $half = ceil($length / 2);
            $bin = static::randomBytes($half);
            $code = bin2hex($bin);
        } catch (\Exception) {
            $code = null;
        }

        $code = $code ?: md5(microtime() . mt_rand(1, 9999));

        return substr($code, 0, $length);
    }

    /**
     * Generate a random number with the specified length.
     *
     * @param int $len Length of the random number (between 2 and 10).
     * @return string Random number as a string.
     * @throws ValidationException If the length is outside the range of 2 to 10.
     */
    public static function randomNumber(int $len): string
    {
        if ($len < 2 || $len > 10) {
            throw ValidationException::of('The random number must have a length between 2 and 10 digits.');
        }

        // Generate a random number within the specified length range
        $min = pow(10, $len - 1); // Minimum number with specified length
        $max = ($len < 10) ? pow(10, $len) - 1 : PHP_INT_MAX; // Maximum number with specified length
        $randomNumber = mt_rand($min, $max);

        return (string)$randomNumber;
    }

    public static function randomLetters(int $length): string
    {
        return substr(md5(static::randomCode($length)), 0, $length);
    }


    public function user(): ?User
    {
        return $this->hasOne(User::class, 'user_id');
    }


    /**
     * @throws \Throwable
     */
    /**
     * Replace an existing ConfirmationCode or create a new one.
     *
     * @param ConfirmationCode $code
     * @return ConfirmationCode
     * @throws \Throwable
     */
    public static function replace(self $code): static
    {
        return Db::transaction(function () use ($code) {
            // Find an active ConfirmationCode matching type, user_id, and status
            $existing = static::find([
                'type' => $code->getType(),
                'user_id' => $code->getUserId(),
                'status' => 'active',
            ]);

            if ($existing) {
                // Update the existing ConfirmationCode with new code and deleted_at time
                return static::update($existing, [
                    'code' => $code->getCode(),
                    'deleted_at' => $code->getDeletedAt(),
                ]);
            } else {
                // Create a new ConfirmationCode if no active one exists
                return static::create($code->toArray());
            }
        }, static::class);
    }
}