<?php

namespace Autumn\Extensions\Auth\Forms;

use Autumn\Extensions\Auth\Models\User\User;
use Autumn\System\Requests\FormRequest;

class RegistrationForm extends FormRequest
{
    protected array $rules = [
        'name' => 'string|max:255',
        'email' => 'required|email|unique:users|max:255',
        'username' => 'required|unique:users|max:255',
        'password' => 'required|string|min:4',
        //'confirm_password' => 'required|same:password',
        'terms' => 'accepted', // Example rule for accepting terms of service
    ];

    protected array $registeredClasses = [
        'users' => User::class
    ];
}