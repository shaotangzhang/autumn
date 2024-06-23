<?php

namespace Autumn\System;

use Autumn\System\Responses\RedirectResponse;
use Autumn\System\Responses\ThrowableResponse;
use Autumn\System\Templates\TemplateService;

class Controller
{
    public const METHOD_GET = 'index';
    public const METHOD_POST = 'post';
    public const METHOD_PUT = 'put';
    public const METHOD_PATCH = 'path';
    public const METHOD_HEAD = 'head';
    public const METHOD_DELETE = 'delete';
    public const METHOD_OPTIONS = 'options';
    public const METHOD_TRACE = 'trace';
    public const METHOD_CONNECT = 'connect';

    protected string $viewPath = '';

    public function view(string $name, array $args = null, array $context = null): View
    {
        if (!str_starts_with($name, '/')) {
            $name = $this->viewPath . $name;
        }

        $context['use_layout'] ??= !!env('THEME_USE_LAYOUT');
        return new View($name, $args, $context);
    }

    public function redirect(string $redirect, int $code = 302, string $message = null): Response
    {
        return new RedirectResponse($redirect, $code, $message);
    }

    public function render(mixed $data, array|\ArrayAccess $args = null, array $context = null): mixed
    {
        return app(TemplateService::class)->output($data, $args, $context);
    }

    public function respond(callable $callback, array $args = null, array $context = null): Response
    {
        try {
            return call($callback, $args, $context);
        } catch (\Throwable $ex) {
            $result = new ThrowableResponse($ex);
            $result->setContext($context);
            return $result;
        }
    }
}