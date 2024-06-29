<?php

namespace Autumn\Database\Models;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RepositoryTrait;
use Autumn\Exceptions\NotFoundException;

class Repository implements \IteratorAggregate, RepositoryInterface
{
    use RepositoryTrait;

    public function first(): mixed
    {
        return $this->__affective_result__()?->fetch();
    }

    public function firstOrFail(string $error = null): mixed
    {
        return $this->first() ?? throw NotFoundException::of($error);
    }
}