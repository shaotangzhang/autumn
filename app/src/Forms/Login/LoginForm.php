<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace App\Forms\Login;

use Autumn\System\Requests\FormRequest;
use Autumn\System\Requests\SubmissionHandler;
use Autumn\System\Service;

class LoginForm extends FormRequest
{
    protected array $rules = [
        'email' => 'require|email',
        'password' => 'require|min:8',
    ];

    public function submit(string|callable|SubmissionHandler $handler, array $args = null, array $context = null): mixed
    {
        if (is_callable($handler)) {
            $context['callable'] = $handler;
            return call($handler, $args, $context);
        }

        if (is_subclass_of($handler, SubmissionHandler::class)) {
            if (is_string($handler)) {
                $handler = app($handler);
            }

            return $handler->submit($this, $context);
        }

        throw new \InvalidArgumentException('Invalid submission handler.');
    }
}