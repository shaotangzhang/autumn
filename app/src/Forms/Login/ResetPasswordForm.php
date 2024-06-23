<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace App\Forms\Login;

use Autumn\Exceptions\ValidationException;
use Autumn\System\Requests\FormRequest;

class ResetPasswordForm extends FormRequest
{
    protected array $rules = [
        'email' => 'require|email',
        'token' => 'require',
        'password' => 'require|min:8',
        'confirm_password' => 'require|same:password',
    ];

    protected function ruleOfSame(string $name, mixed $value, string $compareField): void
    {
        if ($value !== $this->offsetGet($compareField)) {
            throw ValidationException::of('`%s` must match `%s`.', $name, $compareField);
        }
    }
}