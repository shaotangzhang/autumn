<?php

namespace Autumn\System\Sessions;

use Psr\SimpleCache\CacheInterface;

interface SessionInterface extends CacheInterface, \IteratorAggregate
{
    public function id(): string;

    public function open(array $options = null): void;

    public function abort(): void;

    public function close(): void;

    public function destroy(): void;
}