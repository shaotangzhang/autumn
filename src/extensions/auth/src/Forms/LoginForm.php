<?php

namespace Autumn\Extensions\Auth\Forms;

use Autumn\System\Requests\FormRequest;

class LoginForm extends FormRequest
{
    protected array $rules = [
        'username' => 'required|max:255',
        'password' => 'required|string|min:4'
    ];
}