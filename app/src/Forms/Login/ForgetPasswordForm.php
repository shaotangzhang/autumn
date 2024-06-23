<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace App\Forms\Login;

use Autumn\System\Requests\FormRequest;

class ForgetPasswordForm extends FormRequest
{
    protected array $rules = [
        'email' => 'require|email',
    ];
}