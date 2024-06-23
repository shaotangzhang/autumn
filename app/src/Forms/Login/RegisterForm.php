<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace App\Forms\Login;

use Autumn\Exceptions\ValidationException;
use Autumn\System\Requests\FormRequest;

class RegisterForm extends FormRequest
{
    protected array $rules = [
        'username' => 'require|min:3|max:20',
        'email' => 'require|email',
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