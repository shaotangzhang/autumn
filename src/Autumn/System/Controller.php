<?php

namespace Autumn\System;

use Autumn\Interfaces\ContextInterface;
use Autumn\Traits\ContextInterfaceTrait;

class Controller implements ContextInterface
{
    use ContextInterfaceTrait;

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
}